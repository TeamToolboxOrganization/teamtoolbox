<?php

namespace App\Controller;

use App\Entity\MindsetDTO;
use App\Entity\Note;
use App\Entity\Office;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\ConfigurationRepository;
use App\Repository\CustomColorRepository;
use App\Repository\MepRepository;
use App\Repository\MindsetRepository;
use App\Repository\NoteRepository;
use App\Repository\O3Repository;
use App\Repository\OfficeRepository;
use App\Repository\SquadRepository;
use App\Repository\UserRepository;
use App\Repository\VacationRepository;
use App\Security\CSPDefinition;
use DateInterval;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Embera\Embera;

#[Route('/dashboard')]
#[IsGranted('ROLE_SCREEN')]

class DashboardController extends AbstractController
{
    /**
     * Cache(smaxage="10")
     */
    #[Route("/",name: "dashboard_index", defaults: ["page" => "1", "_format" => "html"])]
    public function index(Request $request, OfficeRepository $officeRepository, MsGraphController $msTokenController, O3Repository $o3Repository, CalendarController $calendarController, ConfigurationRepository $configurationRepository, NoteRepository $notes, MindsetRepository $mindsetRepository, UserRepository $users, MepRepository $mepRepository, ManagerRegistry $managerRegistry, Security $security, VacationRepository $vacationRepository): Response
    {
        /**
         * @var $currentUser User
         */
        $currentUser = $this->getUser();

        $toDiscussNotes = $notes->findBy([
            'author' => $currentUser,
            'type' => [Note::TYPE_TODISCUSS, Note::TYPE_TODO],
            'checked' => 0,
        ], ['publishedAt' => 'DESC',], 20);

        $mindsetGlobal = new MindsetDTO(0, 0);
        if ($security->isGranted('ROLE_MANAGER')) {
            $mindsetGlobal = $mindsetRepository->getMindsetGlobal();
        }

        $nextMeps = $mepRepository->getNextMeps();
        $userNextMeps = $mepRepository->getNextMepUser($currentUser->getId());
        $nextMep = $mepRepository->getNextMeps(1);

        $nextBirthdays = $users->getNextBirthdaysUsers(4);
        $missedBirthdays = $users->getMissedBirthays();

        // Get start and end of week
        $startOfWeek = new \DateTime('now -1 day');
        $endOfWeek = new \DateTime('now +6 day');
        $officeDates = $officeRepository->getOfficeDateBetweenDate($startOfWeek, $endOfWeek, null, false);

        $officeByDate = [];
        $dateIterator = new \DateTime('now');
        for ($i = 1; $i <= 7; $i++) {
            $currentDate = date_format($dateIterator, "Y/m/d");
            $officeByDate[$currentDate][] = [];
            $dateIterator = $dateIterator->add(DateInterval::createFromDateString('1 day'));
        }

        /**
         * @var $officeDate Office
         */
        foreach ($officeDates as $officeDate){
            $currentDate = date_format($officeDate->getStartAt(), "Y/m/d");
            if(array_key_exists($currentDate,$officeByDate)){
                $officeByDate[$currentDate][] = [$officeDate->getCollab(), $officeDate->getAmPm()];
            } else {
                $officeByDate[$currentDate] = [[$officeDate->getCollab(), $officeDate->getAmPm()]];
            }
        }

        $showWizard = $request->query->get('showWizard') || $currentUser->isWizard();

        if ($currentUser->isWizard()) {
            $currentUser->setWizard(false);
            $em = $managerRegistry->getManager();
            $em->persist($currentUser);
            $em->flush();
        }

        $eventsOutlook = $calendarController->getMsCalendar($msTokenController, $managerRegistry, $currentUser);
        $o3List = $o3Repository->getO3AfterStartDate($currentUser, new \DateTime('now'));

        /*
        $config = [
            'responsive' => true
        ];
        $embera = new Embera($config);
        $linkToRead = $configurationRepository->findOneBy(['key' => 'media_content_url']);

        $externalContent = null;
        $externalTitle = null;

        if($linkToRead){
            $data = $embera->getUrlData([
                $linkToRead->getValue(),
            ]);

            $externalContent = $embera->autoEmbed($linkToRead->getValue());
            $externalTitle = $data[$linkToRead->getValue()]['title'];
        }
        */

        $todayDate = new \DateTime('today');
        $todayVacations = $vacationRepository->getCurrentVacation($todayDate);
        date_modify($todayDate, '+1 day');
        $next_day = "vacation.tomorrow";
        if($todayDate->format('l') == 'Saturday'){
            date_modify($todayDate, '+2 day');
            $next_day = "vacation.monday";
        }
        if($todayDate->format('l') == 'Sunday'){
            date_modify($todayDate, '+1 day');
            $next_day = "vacation.monday";
        }
        $tomorrowVacations = $vacationRepository->getCurrentVacation($todayDate);

        // Affichage des widgets concernant les congés
        if ($security->isGranted('ROLE_MANAGER')) {
            $vacationsToManage = [];
            $vacationsToManage = $vacationRepository->getVacations($currentUser->getId(), true);
            return new Response(
                $this->renderView('dashboard/dashboard.html.twig', [
                    'toDiscussNotes' => $toDiscussNotes,
                    'nextBirthdays' => $nextBirthdays,
                    'missedBirthdays' => $missedBirthdays,
                    'nextMepDates' => $nextMeps,
                    'myNextMepDates' => $userNextMeps,
                    'nextMep' => !empty($nextMep) ? $nextMep[0] : null,
                    'mindset' => $mindsetGlobal,
                    'officeDates' => $officeDates,
                    'officeByDate' => $officeByDate,
                    'showWizard' => $showWizard,
                    'externalContent' => '',
                    'externalTitle' => '',
                    'events' => $eventsOutlook,
                    'o3List' => $o3List,
                    'vacationsToManage' => $vacationsToManage,
                    'todayVacations' => $todayVacations,
                    'tomorrowVacations' => $tomorrowVacations,
                    'nextVacationDay' => $next_day
                ]),
                Response::HTTP_OK,
                [
                    'Content-Security-Policy-Report-Only' => CSPDefinition::defaultRules
                ]
            );
        }

        return new Response(
            $this->renderView('dashboard/dashboard.html.twig', [
                'toDiscussNotes' => $toDiscussNotes,
                'nextBirthdays' => $nextBirthdays,
                'missedBirthdays' => $missedBirthdays,
                'nextMepDates' => $nextMeps,
                'myNextMepDates' => $userNextMeps,
                'nextMep' => !empty($nextMep) ? $nextMep[0] : null,
                'mindset' => $mindsetGlobal,
                'officeDates' => $officeDates,
                'officeByDate' => $officeByDate,
                'showWizard' => $showWizard,
                'externalContent' => '',
                'externalTitle' => '',
                'events' => $eventsOutlook,
                'o3List' => $o3List,
                'todayVacations' => $todayVacations,
                'tomorrowVacations' => $tomorrowVacations,
                'nextVacationDay' => $next_day
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy-Report-Only' => CSPDefinition::defaultRules
            ]
        );
    }

    #[Route("/mindset/history/",name: "global_mindset_history")]
    #[IsGranted('ROLE_MANAGER')]
    public function getMindsetHistory(MindsetRepository $mindsetRepository): Response
    {
        $result = $mindsetRepository->getMindsetHistoryGlobal();

        return $this->render('mindset/mindsetHistory.json.twig', [
            'mindsetHistory' => $result,
        ]);
    }


    #[Route("/checknote/{checked}",name: "note_check")]
    public function checkNote(Request $request, ManagerRegistry $managerRegistry, string $checked, NoteRepository $notes): Response
    {
        /**
         * @var User
         */
        $currentUser = $this->getUser();
        $noteId = $request->query->get('noteId');

        /**
         * @var Note $note
         */
        $note = $notes->findOneBy([
            'id' => $noteId,
            'author' => $currentUser,
        ]);

        if ($checked == "checked") {
            if ($note->getChecked() == 1) {
                return new JsonResponse(["error" => "Note already checked"], Response::HTTP_NOT_ACCEPTABLE);
            }
            $note->setChecked(1);
        } elseif ($checked == "unchecked") {
            if ($note->getChecked() == 0) {
                return new JsonResponse(["error" => "Note already unchecked"], Response::HTTP_NOT_ACCEPTABLE);
            }
            $note->setChecked(0);
        } else {
            return new JsonResponse(["error" => "Bad param, use checked or unchecked"], Response::HTTP_BAD_REQUEST);
        }

        $em = $managerRegistry->getManager();
        $em->persist($note);
        $em->flush();

        return new JsonResponse(["action" => "updated"], Response::HTTP_OK);
    }
}
