<?php

namespace App\Controller;

use App\Entity\O3;
use App\Entity\User;
use App\Repository\O3Repository;
use App\Repository\UserRepository;
use App\Security\CSPDefinition;
use App\Utils\MsTimeZones;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Attendee;
use Microsoft\Graph\Model\DateTimeTimeZone;
use Microsoft\Graph\Model\EmailAddress;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Microsoft\Graph\Model\Event;

#[Route("/o3")]
class O3Controller extends AbstractController
{
    #[Route("/list/{idManager}", name: "o3_list", methods: ["GET"])]
    public function getO3ListForManager(O3Repository $o3Repository, UserRepository $userRepository, int $idManager): Response
    {
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $manager = $userRepository->find($idManager);
        $o3List = $o3Repository->findBy(['collab' => $idManager]);

        return new Response(
            $this->renderView('o3/o3List.html.twig', [
                'o3List' => $o3List,
                'manager' => $manager,
            ]),
            Response::HTTP_OK,
            [
                //'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    #[Route("/import",name: "import_o3_slot")]
    public function importo3Slot(MsGraphController $msTokenController, ManagerRegistry $managerRegistry): Response
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
        $startDate = new \DateTimeImmutable('sunday -1 week', $timezone);
        //$endDate = new \DateTimeImmutable('sunday +3 week', $timezone);

        $queryParams = array(
            // Only request the properties used by the app
            '$select' => 'responseStatus, showAs, subject,organizer,start,end,categories',
            //'startDateTime' => $startDate->format(\DateTimeInterface::ISO8601),
            //'endDateTime' => $endDate->format(\DateTimeInterface::ISO8601),
            // Sort them by start time
            '$orderby' => 'start/dateTime',
            '$filter' => "subject eq 'O3' and start/dateTime ge '" . $startDate->format(\DateTimeInterface::ISO8601) . "'",
            // Limit results to 25
            '$top' => 25
        );

        // Append query parameters to the '/me/calendarView' url
        $getEventsUrl = '/users/' . $currentUser->getEmail() . '/events?' . http_build_query($queryParams);

        $events = $graph->createRequest('GET', $getEventsUrl)
            // Add the user's timezone to the Prefer header
            ->addHeaders(array(
                'Prefer' => 'outlook.timezone="' . $currentUser->getMsToken()->getUserTimeZone() . '"'
            ))
            ->setReturnType(Event::class)
            ->execute();

        $results = [];

        $managerO3 = $managerRegistry->getManagerForClass(O3::class);
        $o3Repository = $managerO3->getRepository(O3::class);

        /**
         * @var $event Event
         */
        foreach ($events as $event) {
            $existingO3 = $o3Repository->findBy([
                'collab' => $currentUser,
                'startAt' => (new \DateTime($event->getStart()->getDateTime())),
                'endAt' => (new \DateTime($event->getEnd()->getDateTime())),
            ]);

            if(!$existingO3){
                $newO3 = new O3();
                $newO3->setCollab($currentUser);
                $newO3->setStartAt(new \DateTime($event->getStart()->getDateTime()));
                $newO3->setEndAt(new \DateTime($event->getEnd()->getDateTime()));

                //$newO3->setOutlookEventId($event->getId());
                $managerO3->persist($newO3);
                $results[] = $newO3;
            }

        }

        $managerO3->flush();

        return $this->redirectToRoute('o3_list', ['idManager' => $currentUser->getId()]);

        /**return new Response(
            $this->renderView('o3/o3List.html.twig', [
                'o3List' => $results,
                'manager' => $currentUser,
                'title' => 'Nouveaux créneaux O3',
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy-Report-Only' => CSPDefinition::defaultRules
            ]
        );*/

    }

    #[Route("/booking/{idO3}",name: "booking_03_slot", methods: ["GET"])]
    public function bookingO3Slot(Request $request, MsGraphController $msTokenController, ManagerRegistry $managerRegistry, O3Repository $o3Repository, int $idO3): Response
    {
        /**
         * @var $currentUser User
         */
        $currentUser = $this->getUser();

        /**
         * @var $selectedO3 O3
         */
        $selectedO3 = $o3Repository->find($idO3);

        $bookedCollaborator = $selectedO3->getCollaborator();

        $result = "booked";
        $createOutlookEvent = true;
        $deleteOutlookEvent = false;

        if($bookedCollaborator != null){
            if($bookedCollaborator === $currentUser){
                $selectedO3->setCollaborator(null);
                $result = "Slot will be now free";
                $deleteOutlookEvent = true;
            } else {
                $result = "this slot is already booked by another collaborator";
                $createOutlookEvent = false;
            }
        } else {
            $selectedO3->setCollaborator($currentUser);
        }

        // Get MS Graph access for user<
        $graph = $this->getGraph($currentUser, $msTokenController, $managerRegistry);
        if($deleteOutlookEvent){
            $getEventsUrl = '/me/events/' . $selectedO3->getOutlookEventId();
            $graph->createRequest('DELETE', $getEventsUrl)
                ->execute();

            // Update O2 entity
            $emO3 = $managerRegistry->getManagerForClass(O3::class);
            $selectedO3->setOutlookEventId(null);
            $emO3->persist($selectedO3);
            $emO3->flush();

        } elseif($createOutlookEvent){
            // Get MS Graph access for user<
            $graph = $this->getGraph($currentUser, $msTokenController, $managerRegistry);

            // Remove 2 hours to $date
            $tmpDate = $this->removeTwoHours($selectedO3->getStartAt());

            $start = new DateTimeTimeZone();
            $start->setDateTime($tmpDate->format(\DateTimeInterface::ISO8601));
            $start->setTimeZone($currentUser->getMsToken()->getUserTimeZone());

            // Remove 2 hours to $date
            $tmpDate = $this->removeTwoHours($selectedO3->getEndAt());

            $end = new DateTimeTimeZone();
            $end->setDateTime($tmpDate->format(\DateTimeInterface::ISO8601));
            $end->setTimeZone($currentUser->getMsToken()->getUserTimeZone());

            $manager = $currentUser->getManager();
            $managerAttendee = new Attendee();
            $managerMail = new EmailAddress();
            $managerMail->setAddress($manager->getEmail());
            $managerMail->setName($manager->getFullName());
            $managerAttendee->setEmailAddress($managerMail);

            $queryParams = array(
                'subject' => "O3 : " . $currentUser->getFullName(),
                'start' => $start,
                'end' => $end,
                'isOnlineMeeting' => true,
                'onlineMeetingProvider' => 'teamsForBusiness',
                'attendees' => [$managerAttendee],
            );

            // Append query parameters to the url
            $getEventsUrl = '/me/events/?' . http_build_query($queryParams);

            /**
             * @var $createdEvent Event
             */
            $createdEvent = $graph->createRequest('POST', $getEventsUrl)
                ->addHeaders(array(
                    'Prefer' => 'outlook.timezone="' . $currentUser->getMsToken()->getUserTimeZone() . '"'
                ))
                ->attachBody($queryParams)
                ->setReturnType(Event::class)
                ->execute();

            // Update O2 entity
            $emO3 = $managerRegistry->getManagerForClass(O3::class);
            $selectedO3->setOutlookEventId($createdEvent->getId());
            $emO3->persist($selectedO3);
            $emO3->flush();
        }

        return $this->redirectToRoute('o3_list', ['idManager' => $currentUser->getId()]);

        /*return new JsonResponse([
            "action" => $result,
            "idO3" => $idO3,
            "idEvent" => $selectedO3->getOutlookEventId(),
        ], Response::HTTP_OK);*/
    }

    private function removeTwoHours(DateTime $inputDate): DateTime|false
    {
        $tmpDate = $inputDate;

        // Remove 2 hours to $date
        return $tmpDate->modify('-2 hours');
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
}