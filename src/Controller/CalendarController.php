<?php

namespace App\Controller;

use App\Entity\MsEventDTO;
use App\Entity\O3;
use App\Entity\User;
use App\Form\XlsxExport;
use App\Repository\CustomColorRepository;
use App\Repository\MepRepository;
use App\Repository\O3Repository;
use App\Repository\OfficeRepository;
use App\Security\CSPDefinition;
use App\Utils\MsTimeZones;
use App\Repository\UserRepository;
use App\Repository\VacationRepository;
use App\Repository\CategoryRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Unirest;

#[Route("/calendar")]
class CalendarController extends AbstractController
{
    #[Route("/",name: "calendar_index")]
    public function index(Security $security, int $userId = null): Response
    {
        return new Response(
            $this->renderView('calendar/index.html.twig', [

            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy-Report-Only' => CSPDefinition::defaultRules
            ]
        );
    }

    private function showOnlyType(UserRepository $users, int $itemId, string $itemType, User $currentUser): bool
    {
        if ($itemId === 0) {
            $onlyType = false;
        } else if ($itemType == 'squad') {
            $onlyType = false;
        } else {
            $onlyType = ($itemId == $currentUser->getId());
            if ($onlyType) {
                return true;
            }

            try {
                $collabUser = $users->find($itemId);

                if ($collabUser->getManager() != null) {
                    return ($currentUser->getId() == $collabUser->getManager()->getId());
                }
            } catch (\Exception $exception) {
                return false;
            }
        }

        return false;
    }

    #[Route("/data/{itemType}/{itemId}",name: "calendar_data")]
    #[Cache(maxage: 600, public: true)]
    public function datas(Request $request, UserRepository $users, OfficeRepository $officeRepository, MepRepository $mepRepository, VacationRepository $vacations, Security $security, string $itemType = null, int $itemId = null): Response
    {
        /**
         * @var User $collabUser
         */
        $currentUser = $security->getUser();
        $onlyType = $this->showOnlyType($users, $itemId, $itemType, $currentUser);

        $allDates = $request->get("allDates");

        $startDate = $request->get("start");
        if ($startDate) {
            $startDate = new \DateTime($startDate);
        }

        $endDate = $request->get("end");
        if ($endDate) {
            $endDate = new \DateTime($endDate);
        }

        $typeRequest = $request->get("type");

        if ($typeRequest == 'birthdays') {
            $birthdays = $this->getBirthdaysDates($users, $itemId, $itemType, $allDates);

            return $this->render('calendar/calendarDataBirthday.json.twig', [
                'birthdays' => $birthdays,
                'onlyType' => $onlyType,
            ]);
        } elseif ($typeRequest == 'vacations') {
            $collabVacations = $this->getVacationsDates($vacations, $users, $itemId, $itemType, $allDates, $startDate, $endDate);
            $holidays = $this->getHollidaysDates();

            return $this->render('calendar/calendarDataVacation.json.twig', [
                'vacations' => $collabVacations,
                'holidays' => $holidays,
                'onlyType' => $onlyType,
            ]);

        } elseif ($typeRequest == 'Bureau') {
            $userDates = $this->getOfficeDates($officeRepository, $users, $itemId, $itemType, $allDates, $startDate, $endDate, false);

            return $this->render('calendar/calendarDataOffice.json.twig', [
                'userDates' => $userDates,
                'onlyType' => $onlyType,
            ]);
        } elseif ($typeRequest == 'Télétravail') {
            $userDates = $this->getOfficeDates($officeRepository, $users, $itemId, $itemType, $allDates, $startDate, $endDate, true);

            return $this->render('calendar/calendarDataOffice.json.twig', [
                'userDates' => $userDates,
                'onlyType' => $onlyType,
            ]);
        } elseif ($typeRequest == 'MEP') {
            $userDates = $this->getMepDates($mepRepository, $users, $itemId, $itemType, $allDates, $startDate, $endDate);

            return $this->render('calendar/calendarDataMep.json.twig', [
                'userDates' => $userDates,
                'onlyType' => $onlyType,
            ]);
        } elseif ($typeRequest) {
            $userDates = [];
            return $this->render('calendar/calendarDataUserDate.json.twig', [
                'userDates' => $userDates,
                'onlyType' => $onlyType,
            ]);
        }

        return new Response(null);
    }

    private function getMepDates(MepRepository $mepRepository, UserRepository $users, int $itemId, string $itemType, bool $allDates, \DateTime $startDate, \DateTime $endDate): array
    {
        $userDates = [];
        $isSquad = ($itemType == 'squad');

        if ($isSquad) {
            // Values for user of squad
            $allUsers = $users->getUsersForSquad($itemId);
            foreach ($allUsers as $user) {
                $userDates = array_merge($userDates, $mepRepository->getMepBetweenDate($startDate, $endDate, $user->getId()));
            }
        } else {
            if ($allDates) {
                // All values
                $userDates = $mepRepository->getMepBetweenDate($startDate, $endDate, null);
            } else {
                // Values only for selected user
                $userDates = array_merge($userDates, $mepRepository->getMepBetweenDate($startDate, $endDate, $itemId));
            }
        }

        return $userDates;
    }

    private function getHollidaysDates()
    {
        $gouvUser = $this->getParameter('gouv_api_user');
        $gouvKey = $this->getParameter('gouv_api_key');

        $holidays = [];

        if($gouvUser == null || $gouvKey == null){
            return JSONResponse::fromJsonString(json_encode($holidays));
        }

        try {
            Unirest\Request::auth($gouvUser, $gouvKey);

            $headers = array(
                'Accept' => 'application/json'
            );

            $responseData = Unirest\Request::get(
                'https://calendrier.api.gouv.fr/jours-feries/metropole/' . date("Y") . '.json',
                $headers,
            );

            foreach ($responseData->body as $value => $label) {
                $holidays[] = ["startAt" => $value, "label" => $label];
            }

        } catch (\Exception $exception) {
            // DO nothing
        }
        return $holidays;
    }

    /**
     * @param VacationRepository $vacationRepository
     * @param UserRepository $users
     * @param int $itemId
     * @param string $itemType
     * @param bool $allDates
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return array
     */
    private function getVacationsDates(VacationRepository $vacationRepository, UserRepository $users, int $itemId, string $itemType, bool $allDates, \DateTime $startDate, \DateTime $endDate): array
    {
        $userDates = [];
        $isSquad = ($itemType == 'squad');

        if ($isSquad) {
            // Values for user of squad
            $allUsers = $users->getUsersForSquad($itemId);
            foreach ($allUsers as $user) {
                $userDates = array_merge($userDates, $vacationRepository->getVacationBetweenDate($startDate, $endDate, $user->getId()));
            }
        } else {
            if ($allDates) {
                // All values
                $userDates = $vacationRepository->getVacationBetweenDate($startDate, $endDate, null);
            } else {
                // Values only for selected user
                $userDates = array_merge($userDates, $vacationRepository->getVacationBetweenDate($startDate, $endDate, $itemId));
            }
        }

        return $userDates;
    }

    /**
     * @param UserRepository $users
     * @param int $itemId
     * @param string $itemType
     * @param bool $allDates
     * @return array
     */
    private function getBirthdaysDates(UserRepository $users, int $itemId, string $itemType, bool $allDates): array
    {
        $userDates = [];
        $allUsers = [];
        $isSquad = ($itemType == 'squad');

        if ($isSquad) {
            $allUsers = $users->findBy([
                'squad' => $itemId,
                'sharedata' => true
            ]);
        } else {
            if ($allDates) {
                // All values
                $allUsers = $users->findBy([
                    'sharedata' => true
                ]);
            } else {
                // Values only for selected user
                $collabUser = $users->findOneBy([
                    'id' => $itemId,
                    'sharedata' => true
                ]);
                if ($collabUser != null) {
                    array_push($allUsers, $collabUser);
                }
            }
        }

        foreach ($allUsers as $user) {
            if($user->getBirthday() != null){
                $userDates[] = [$user->getFullName(), $user->getBirthday()->format(date("Y")."-m-d"), $user->getId()];
            }
        };

        return $userDates;
    }

    /**
     * @param OfficeRepository $officeRepository
     * @param UserRepository $users
     * @param int $itemId
     * @param string $itemType
     * @param bool $allDates
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return array
     */
    private function getOfficeDates(OfficeRepository $officeRepository, UserRepository $users, int $itemId, string $itemType, bool $allDates, \DateTime $startDate, \DateTime $endDate, bool $isHomeOffice): array
    {
        $userDates = [];
        $isSquad = ($itemType == 'squad');

        if ($isSquad) {
            // Values for user of squad
            $allUsers = $users->getUsersForSquad($itemId);
            foreach ($allUsers as $user) {
                $userDates = array_merge($userDates, $officeRepository->getOfficeDateBetweenDate($startDate, $endDate, $user->getId(), $isHomeOffice));
            }
        } else {
            if ($allDates) {
                // All values
                $userDates = $officeRepository->getOfficeDateBetweenDate($startDate, $endDate, null, $isHomeOffice);
            } else {
                // Values only for selected user
                $userDates = array_merge($userDates, $officeRepository->getOfficeDateBetweenDate($startDate, $endDate, $itemId, $isHomeOffice));
            }
        }

        return $userDates;
    }

    public function getMsCalendar(MsGraphController $msTokenController, ManagerRegistry $managerRegistry, $userId = null): array
    {
        /**
         * @var $currentUser User
         */
        $currentUser = $this->getUser();

        // Get MS Graph access for user
        $graph = $this->getGraph($currentUser, $msTokenController, $managerRegistry);

        // Get user's timezone
        $timezone = MsTimeZones::getTzFromWindows($currentUser->getMsToken()->getUserTimeZone());

        // Get start and end of week
        $startOfWeek = new \DateTimeImmutable('now', $timezone);
        $endOfWeek = new \DateTimeImmutable('tomorrow', $timezone);

        return $this->getMeettings($graph, $currentUser, $timezone, $startOfWeek, $endOfWeek);
    }

    #[Route("/msgraph/event/update",name: "msgraph_update_event", methods: ["POST"])]
    public function updateEventMsGraph(Request $request, MsGraphController $msTokenController, ManagerRegistry $managerRegistry): Response
    {
        $eventId = $request->get("eventId");

        if($eventId == null){
            return new JsonResponse([
                "error" => "you must specified event id to update",
            ], Response::HTTP_BAD_REQUEST);
        }

        $subject = $request->get("subject");

        /**
         * @var $currentUser User
         */
        $currentUser = $this->getUser();

        // Get MS Graph access for user
        $graph = $this->getGraph($currentUser, $msTokenController, $managerRegistry);

        $queryParams = array(
            'subject' => $subject,
        );

        // Append query parameters to the url
        $getEventsUrl = '/me/events/' . $eventId. '?' . http_build_query($queryParams);

        $graph->createRequest('PATCH', $getEventsUrl)
            ->attachBody($queryParams)
            ->execute();

        return new JsonResponse([
            "action" => "updated",
            "idEvent" => $eventId,
        ], Response::HTTP_OK);
    }

    private function deleteOutlookEvent(User $user, MsGraphController $msTokenController, ManagerRegistry $managerRegistry, string $eventId): void
    {
        // Get MS Graph access for user
        $graph = $this->getGraph($user, $msTokenController, $managerRegistry);

        $getEventsUrl = '/me/events/' . $eventId;

        $graph->createRequest('DELETE', $getEventsUrl)
            ->execute();
    }


    private function getGraph(User $user, MsGraphController $msTokenController, ManagerRegistry $managerRegistry): Graph
    {
        // Get the access token from the cache
        $accessToken = $msTokenController->getAccessToken($user, $managerRegistry);

        // Create a Graph client
        $graph = new Graph();
        $graph->setAccessToken($accessToken);
        return $graph;
    }

    /**
     * Combine a number of DateIntervals into 1
     * @param DateInterval $...
     * @return DateInterval
     */
    function addDateIntervals()
    {
        $reference = new \DateTime();
        $endTime = clone $reference;

        foreach (func_get_args() as $dateInterval) {
            $endTime = $endTime->add($dateInterval);
        }

        return $reference->diff($endTime);
    }

    /**
     * Make a prorata between the hours and minutes of an interval by an other
     * @param $timeActivity DateInterval
     * @param $realTime DateInterval
     * @param DateInterval $objectiveTime
     * @return DateInterval|false
     */
    private function prorataIntervals(DateInterval $timeActivity, DateInterval $realTime, DateInterval $objectiveTime){
        $dividendDate = new \DateTime();
        $dividendDate->setTime(0, 0,);
        $divisorDate = clone $dividendDate;
        $objectiveDate = clone $dividendDate;
        $prorataDate = clone $dividendDate;
        $dividendDate->add($timeActivity);
        $divisorDate->add($realTime);
        $objectiveDate->add($objectiveTime);
        $divdH = intval($dividendDate->format("H"));
        $divdM = intval($dividendDate->format("i")) / 60;
        $diviH = intval($divisorDate->format("H"));
        $diviM = intval($divisorDate->format("i")) / 60;
        $objH = intval($objectiveDate->format("H"));
        $objM = intval($objectiveDate->format("i")) / 60;
        $dividend = $divdH + $divdM;
        $divisor = $diviH + $diviM;
        $objective = $objH + $objM;
        $prorata = ($dividend / $divisor) * $objective;
        $prorataDate->setTime($prorata - ($prorata - intval($prorata)), ($prorata - intval($prorata)) * 60);
        $dividendDate->setTime(0, 0,);
        return $dividendDate->diff($prorataDate);
    }

    /**
     * Get MSEvent with MSEventDTO format
     * @param Graph $graph
     * @param User $currentUser
     * @param \DateTimeZone $timezone
     * @param \DateTimeImmutable $startOfWeek
     * @param \DateTimeImmutable $endOfWeek
     * @return array<MsEventDTO>
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    private function getMeettings($graph, $currentUser, $timezone, \DateTimeImmutable $startOfWeek, \DateTimeImmutable $endOfWeek)
    {
        $queryParams = array(
            'startDateTime' => $startOfWeek->format(\DateTimeInterface::ISO8601),
            'endDateTime' => $endOfWeek->format(\DateTimeInterface::ISO8601),
            // Only request the properties used by the app
            '$select' => 'responseStatus, showAs, subject,organizer,start,end,categories',
            // Sort them by start time
            '$orderby' => 'start/dateTime',
            // Limit results to 25
            '$top' => 10000
        );

        // Append query parameters to the '/me/calendarView' url
        $getEventsUrl = '/me/calendarView?' . http_build_query($queryParams);

        $events = $graph->createRequest('GET', $getEventsUrl)
            // Add the user's timezone to the Prefer header
            ->addHeaders(array(
                'Prefer' => 'outlook.timezone="' . $currentUser->getMsToken()->getUserTimeZone() . '"'
            ))
            ->setReturnType(Model\Event::class)
            ->execute();

        $results = [];

        /**
         * @var $event Model\Event
         */
        foreach ($events as $event) {
            $start = new \DateTimeImmutable($event->getStart()->getDateTime(), $timezone);
            $end = new \DateTimeImmutable($event->getEnd()->getDateTime(), $timezone);
            $timeDiff = $start->diff($end);

            $result = new MsEventDTO($start, $end, $timeDiff, $event->getSubject(), $event->getOrganizer()->getEmailAddress()->getName());
            $result->setShowAs($event->getShowAs());
            $result->setResponseStatus($event->getResponseStatus());

            foreach ($event->getCategories() as $category) {
                $result->addCategory($category);
            }
            $results[] = $result;
        }
        return $results;
    }

    #[Route("/weekExport",name: "exportWeek")]
    public function exportweek(MsGraphController $msTokenController)
    {
        $user = $this->getUser();
        $timezone = MsTimeZones::getTzFromWindows($user->getMsToken()->getUserTimeZone());
        $startDate = new \DateTimeImmutable('sunday -1 week', $timezone);
        $endDate = new \DateTimeImmutable('friday', $timezone);
        $parameters = ["03. Development", "781361", $startDate->format("Y-m-d"), $endDate->format("Y-m-d")];
        return $this->redirectToRoute('xls_user', $parameters);
    }

    #[Route("/DLxls",name: "xls_user")]
    public function getXLS(MsGraphController $msTokenController, ManagerRegistry $managerRegistry, CategoryRepository $categoryRepository, Request $request, int $startHour = 9, int $startMin = 0, int $durationInt = 7)
    {
        // Init all parameters
        $parameters = $request->query->all();
        $defaultActivity = $parameters[0];
        $workItem = $parameters[1];
        $startDate = $parameters[2];
        $endDate = $parameters[3];

        // Get user
        /** @var User $user */
        $user = $this->getUser();

        // Get user's timezone
        $timezone = MsTimeZones::getTzFromWindows($user->getMsToken()->getUserTimeZone());

        // Get DateTimeImmutable
        $firstDayDate = $this->getStartDate($startDate, $timezone);
        $lastDayDate = $this->getEndDate($endDate, $timezone);

        // Get Meetings from Outlook
        $graph = $this->getGraph($user, $msTokenController, $managerRegistry);
        $resultsdata = $this->getMeettings($graph, $user, $timezone, $firstDayDate, $lastDayDate);

        // Init Date parameters
        $endDay = new \DateTime($lastDayDate->format("Y-m-d"), new \DateTimeZone($timezone->getName()));
        $startDay = new \DateTime($firstDayDate->format("Y-m-d"), new \DateTimeZone($timezone->getName()));
        $startDay->setTime($startHour, $startMin);

        // Count nb days
        $totalDays = $this->countNbDays($startDay, $endDay, $timezone);

        // Calcul Duration for Activity by day
        $days = $this->calculateDuration($resultsdata, $startDay, $totalDays, $timezone, $categoryRepository);

        $days = $this->calibrateNbHours($days, $timezone, $durationInt, $defaultActivity);

        // Write output Excel file
        $spreadsheet = new Spreadsheet();
        $this->writeExcelFile($spreadsheet, $days, $workItem, $startDay, $startHour, $startMin);

        //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //header('Content-Disposition: attachment; filename=' . $xlsName);
        //$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        //$writer->save('php://output');
        //$spreadsheet->disconnectWorksheets();
        //unset($spreadsheet);
        //return new Response(
        //    "", Response::HTTP_OK);

        $xlsName = "7Pace_" . $user->getId() . ".xlsx";
        $path = $this->getParameter('xlsx_directory');
        $writer = new Xlsx($spreadsheet);
        $writer->save($path . '/' . $xlsName);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        $response = new BinaryFileResponse($path . '/' . $xlsName);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $xlsName);
        return $response;
    }

    #[Route("/exportForm",name: "xlsxExportForm")]
    public function new(EntityManagerInterface $em, Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $activity = $user->getDefaultActivity();
        $productId = $user->getDefaultProduct();

        $defaults = [
            'workActivity' => $activity,
            'workItem' => $productId,
        ];

        $form = $this->createForm(XlsxExport::class, $defaults);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $parameters = [$form->getData()['workActivity'], $form->getData()['workItem'], $form->getData()['startDate']->format("Y-m-d"), $form->getData()['endDate']->format("Y-m-d")];
            //dd($form->getData());
            return $this->redirectToRoute('xls_user', $parameters);
        }

        return new Response(
            $this->renderView('XlsxForm/exportForm.html.twig', [
                'exportForm' => $form->createView()])
            , Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    private function calculateDuration(array $msEventDtos, $startDay, $totalDays, $timezone, CategoryRepository $categoryRepository)
    {
        $days = [];
        $addedDay = new \DateTime($startDay->format("Y-m-d"), new \DateTimeZone($timezone->getName()));
        //We create our 5 days entry, now if we have a day without schedules it'll still have hours in it
        for ($day = 0; $day < $totalDays; $day++) {
            if ($addedDay->format("N") < 6) {
                $days[$addedDay->format("Y-m-d")] = [];
            } else {
                $day -= 1;
            }
            $addedDay->modify("+1 day");
        }
        foreach ($msEventDtos as $rdv) {
            // Check if category exist for this day
            if (array_key_exists(0, $rdv->getCategories())) {
                $startDateKeyTmp = $rdv->getStart()->format("Y-m-d");
                $categoryKeyTmp = $rdv->getCategories()[0];

                $startWithTwoDigits = ctype_digit(substr($categoryKeyTmp, 0, 2));
                if (!$startWithTwoDigits) {
                    continue;
                }
                $digit = substr($categoryKeyTmp, 0, 2);
                $categoryItem = $categoryRepository->findLike($digit);

                if (empty($categoryItem)) {
                    continue;
                }

                $category = $categoryItem->getName();

                $timeDiffTemp = $rdv->getTimeDiff();
                if (!empty($rdv->getCategories()) and (intval($timeDiffTemp->format("%d")) == 0)) {
                    if (array_key_exists($category, $days[$startDateKeyTmp])) {
                        $add = $this->addDateIntervals($days[$startDateKeyTmp][$category], $rdv->getTimeDiff());
                        $days[$startDateKeyTmp][$category] = $add;
                    } else {
                        $days[$startDateKeyTmp][$category] = $rdv->getTimeDiff();
                    }
                }
            }
        }
        return $days;
    }

    private function writeDayInXls($day, $sheet, $workItem, $startDay, &$row)
    {
        foreach ($day as $categorie => $dateCat) {
            $column = "A";
            //Start day in A#
            $cell = $column . $row;
            $sheet->setCellValue($cell, $startDay->format('m/d/Y'));
            //Start hour in B#
            $cell = ++$column . $row;
            $sheet->setCellValue($cell, $startDay->format('H:i'));
            //Duration in C#
            $cell = ++$column . $row;
            $sheet->setCellValue($cell, $dateCat->format("%H:%i"));
            //Work item in D#
            $cell = ++$column . $row;
            $sheet->setCellValue($cell, $workItem);
            //CategoryType in E#
            $cell = ++$column . $row;
            $sheet->setCellValue($cell, $categorie);
            $row = ++$row;//Switch to next line
            $startDay->add($dateCat);
        }

        return $startDay;
    }

    private function writeExcelFile($spreadsheet, $days, $workItem, $startDay, $startHour, $startMin)
    {
        // Write Excel file
        $sheet = $spreadsheet->getActiveSheet();
        $row = "1";
        foreach ($days as $day) {
            // Write rows for a day
            $startDay = $this->writeDayInXls($day, $sheet, $workItem, $startDay, $row);
            $startDay->setTime($startHour, $startMin);
            $startDay->modify("+1 day");
            if (intval($startDay->format("N")) >= 6) {
                $startDay->modify("+" . (8 - intval($startDay->format("N"))) . "days");
            }
        }
    }

    private function calibrateNbHours($days, $timezone, $durationInt, $defaultActivity)
    {
        $initDateTime = new \DateTime();
        $initDateTime->setTimezone($timezone);//DateTime Initial /!\ Penser à le cloner et non pas à l'affecter sinon sa valeur change
        $initDateTime->setTime(0, 0);
        $duration = "PT" . strval($durationInt) . "H";
        $objectif = new \DateInterval($duration);//$repS->diff($repE);
        $results = [];
        foreach ($days as $day => $values) {

            $sumDaily = new \DateInterval("PT0H");
            foreach ($values as $categorie => $dateCat) {
                if (!empty($categorie) and (intval($dateCat->format("%d")) == 0)) {
                    $sumDaily = $this->addDateIntervals($sumDaily, $dateCat);
                }
            }

            $sumDate = clone $initDateTime;
            $objectifDate = clone $initDateTime;
            $sumDate->add($sumDaily);
            $objectifDate->add($objectif);

            foreach ($values as $categorie => $dateCat) {
                $results[$day][$categorie] = $dateCat;
            }

            // We not have 7h
            if ($sumDate < $objectifDate) {
                $sP = clone $initDateTime;
                $eP = clone $initDateTime;
                $sP->add($sumDaily);
                $eP->add($objectif);
                $missingValue = $sP->diff($eP);
                if (array_key_exists($defaultActivity, $values)) {
                    $add = $this->addDateIntervals($values[$defaultActivity], $missingValue);
                    $results[$day][$defaultActivity] = $add;
                } else {
                    $results[$day][$defaultActivity] = $missingValue;
                }
            } elseif ($sumDate > $objectifDate) {
                foreach ($values as $categorie => $dateCat) {
                    $results[$day][$categorie] = $this->prorataIntervals($dateCat, $sumDaily, $objectif);
                }
            }
        }
        return $results;
    }

    private function getStartDate($startDate, \DateTimeZone $timezone): \DateTimeImmutable
    {
        $startDate = new \DateTime($startDate, $timezone);
        return new \DateTimeImmutable($startDate->format("Y-m-d"), $timezone);
    }

    private function getEndDate($endDate, \DateTimeZone $timezone): \DateTimeImmutable
    {
        $endDate = new \DateTime($endDate, $timezone);
        return new \DateTimeImmutable($endDate->format("Y-m-d"), $timezone);
    }

    private function countNbDays(&$startDay, $endDay, $timezone)
    {
        if (intval($startDay->format("N")) >= 6) {
            $startDay->modify("+" . (8 - intval($startDay->format("N"))) . "days");
        }
        if (intval($endDay->format("N")) <= 6) {
            $endDay->modify("+1 day");
        }
        $startDayDate = new \DateTime($startDay->format("Y-m-d"), new \DateTimeZone($timezone->getName()));
        $totalDays = 0;
        while ($startDayDate->format("Y-m-d") != $endDay->format("Y-m-d")) {
            if (intval($startDayDate->format("N")) < 6) {
                $totalDays += 1;
            }
            $startDayDate = $startDayDate->modify("+1day");
        }
        return $totalDays;
    }

    /**
     * @param string $duration => value expected : "week", "month".
     */
    private function getDurationActivities(MsGraphController $msTokenController, ManagerRegistry $managerRegistry,  CategoryRepository $categoryRepository, string $duration, int $startHour = 9, int $startMin = 0, int $durationInt = 7)
    {
        // Get user
        $user = $this->getUser();

        // Init all parameters
        $defaultActivity = $user->getDefaultActivity();

        // Get user's timezone
        $timezone = MsTimeZones::getTzFromWindows($user->getMsToken()->getUserTimeZone());

        // Get DateTimeImmutable
        if ($duration == "week") {
            $firstDay = new \DateTimeImmutable('sunday -1 week', $timezone);
            $lastDayDate = new \DateTimeImmutable('saturday', $timezone);
        } elseif ($duration == "month") {
            $firstDay = new \DateTimeImmutable('first day of this month', $timezone);
            $lastDayDate = new \DateTimeImmutable('last day of this month', $timezone);
        }


        // Get Meetings from Outlook
        $graph = $this->getGraph($user, $msTokenController, $managerRegistry);
        $resultsdata = $this->getMeettings($graph, $user, $timezone, $firstDay, $lastDayDate);


        if (intval($firstDay->format("N")) >= 6) {
            $firstDayDate = $firstDay->modify("+" . (8 - intval($firstDay->format("N"))) . "day");
        } else{
            $firstDayDate = $firstDay;
        }

        // Init Date parameters
        $endDay = new \DateTime($lastDayDate->format("Y-m-d"), new \DateTimeZone($timezone->getName()));
        $startDay = new \DateTime($firstDayDate->format("Y-m-d"), new \DateTimeZone($timezone->getName()));
        $startDay->setTime($startHour, $startMin);

        // Count nb days
        $totalDays = $this->countNbDays($startDay, $endDay, $timezone);

        // Calcul Duration for Activity by day
        $days = $this->calculateDuration($resultsdata, $startDay, $totalDays, $timezone, $categoryRepository);

        return $this->calibrateNbHours($days, $timezone, $durationInt, $defaultActivity);
    }

    /**
     * @param string $duration => value expected : "week", "month".
     */
    #[Route("/planification/{duration}/{format}",name: "current_month_planification")]
    public function getPlanifiedHours(string $duration, MsGraphController $msTokenController, ManagerRegistry $managerRegistry, CategoryRepository $categoryRepository, CustomColorRepository $customColorRepository, string $format = "http")
    {
        $error = "";
        $errorTab = [];

        /**
         * @var User $user
         */
        $user = $this->getUser();

        $categoryList = [];
        $categories = $categoryRepository->findAll();
        for ($i = 0; $i < sizeof($categories); $i++){
            $customColor = $customColorRepository->findOneBy(["categoryId"=>$categories[$i]->getId(),"userId"=>$user->getId()]);
            if($customColor==null){
                $categoryList[$categories[$i]->getName()] = $categories[$i]->getDefaultColor();
            }else{
                $categoryList[$categories[$i]->getName()] = $customColor->getCustomColor();
            }
        }

        $result = [];
        $days = $this->getDurationActivities($msTokenController, $managerRegistry, $categoryRepository, $duration);
        if($format == "http"){
            foreach ($days as $day => $value) {
                foreach ($value as $category => $dateCat) {
                    if (array_key_exists($category, $categoryList)) {
                        if (!array_key_exists($category, $result)) {
                            $result[$category]["time"] = new \DateInterval("PT0H");
                            $result[$category]["time"] = $this->addDateIntervals($result[$category]["time"], $dateCat);
                            $result[$category]["color"] = $categoryList[$category];
                        } else {
                            $result[$category]["time"] = $this->addDateIntervals($result[$category]["time"], $dateCat);
                        }
                    } else {
                        $errorTab[$category] = $category;
                    }
                }
            }

            /*if (!empty($errorTab)) {       // if there is some categories not handled
                $error = "la/les categorie.s [ ";
                foreach ($errorTab as $value => $val) {
                    $error = $error . $val . " ";
                }
                $error = $error . "] existe.nt pas. Veuillez vérifier le nom dans Outlook.";
            }*/
            return $this->render('widget/planification.json.twig', [
                'planification' => $result,
                'error' => $error,
            ]);
        }
        else{
            $user = $this->getUser();
            $timezone = MsTimeZones::getTzFromWindows($user->getMsToken()->getUserTimeZone());
            $startHour = 9;
            $startMin = 0;

            if ($duration == "week") {
                $firstDay = new \DateTimeImmutable('sunday -1 week', $timezone);
            } elseif ($duration == "month") {
                $firstDay = new \DateTimeImmutable('first day of this month', $timezone);
            }

            if (intval($firstDay->format("N")) >= 6) {
                $firstDayDate = $firstDay->modify("+" . (8 - intval($firstDay->format("N"))) . "day");
            } else{
                $firstDayDate = $firstDay;
            }

            $startDay = new \DateTime($firstDayDate->format("Y-m-d"), new \DateTimeZone($timezone->getName()));
            $startDay->setTime($startHour, $startMin);

            $spreadsheet = new Spreadsheet();
            $this->writeExcelFile($spreadsheet, $days, $user->getDefaultProduct(), $startDay, $startHour, $startMin);

            $xlsName = "7Pace_" . $user->getId() . "_".$duration.".xlsx";

            $path = $this->getParameter('xlsx_directory');
            $writer = new Xlsx($spreadsheet);

            $writer->save($path . '/' . $xlsName);

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            $response = new BinaryFileResponse($path . '/' . $xlsName);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $xlsName);
            return $response;
        }
    }
}