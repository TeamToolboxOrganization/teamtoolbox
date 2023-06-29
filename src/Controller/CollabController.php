<?php

namespace App\Controller;

use App\Entity\Mep;
use App\Entity\MindsetDTO;
use App\Entity\Note;
use App\Entity\Office;
use App\Entity\User;
use App\Repository\MepRepository;
use App\Repository\MindsetRepository;
use App\Repository\O3Repository;
use App\Repository\OfficeRepository;
use App\Security\CSPDefinition;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\NoteRepository;
use App\Repository\UserRepository;
use App\Repository\VacationRepository;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\LineChart;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Unirest;

#[IsGranted('ROLE_USER')]
#[Route("/collab")]
class CollabController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route("/office",name: "office_date_selection", methods: ['POST'] )]
    public function officeDateSelection(Request $request, OfficeRepository $officeRepository, ManagerRegistry $managerRegistry): Response
    {
        /**
         * @var $currentUser User
         */
        $currentUser = $this->getUser();

        $officeDateSelected = $request->get('date');
        $officeDateSelected = new \DateTime($officeDateSelected);

        /**
         * @var Office $existingDate
         */
        $existingDate = $officeRepository->findOneBy([
            'collab' => $currentUser,
            'startAt' => $officeDateSelected,
            'importFromRhpi' => 0,
        ]);

        $em = $managerRegistry->getManagerForClass(Office::class);
        if (!$existingDate) {
            $newOfficeDate = new Office();
            $newOfficeDate->setCollab($currentUser);
            $newOfficeDate->setStartAt($officeDateSelected);
            $newOfficeDate->setImportFromRhpi(0);
            $em->persist($newOfficeDate);
            $result = new JsonResponse(["Description" => "New office date", "action" => "add", "userid" => $currentUser->getId(), "text" => $currentUser->getFullName()], Response::HTTP_OK);
        } else {
            switch ($existingDate->getAmPm()) {
                case null:
                    $existingDate->setAmPm(1);
                    $em->persist($existingDate);
                    $result = new JsonResponse(["Description" => "Set office date to AM", "action" => "update", "userid" => $currentUser->getId(), "text" => $currentUser->getFullName(), "ampm" => "Matin"], Response::HTTP_OK);
                    break;
                case 1:
                    $existingDate->setAmPm(2);
                    $em->persist($existingDate);
                    $result = new JsonResponse(["Description" => "Set office date to PM", "action" => "update", "userid" => $currentUser->getId(), "text" => $currentUser->getFullName(), "ampm" => "Aprem"], Response::HTTP_OK);
                    break;
                case 2:
                    if($existingDate->getImportFromRhpi() === 0){
                        $em->remove($existingDate);
                        $result = new JsonResponse(["Description" => "Delete office date", "action" => "delete", "userid" => $currentUser->getId(), "text" => $currentUser->getFullName()], Response::HTTP_OK);
                    }
                    break;
            }
        }

        $em->flush();

        return $result;
    }

    #[Route("/{userId}",name: "collab_index")]
    public function index(NoteRepository $notes, UserRepository $users, OfficeRepository $officeRepository, MepRepository $mepRepository, MindsetRepository $mindsetRepository, O3Repository $o3Repository, ?int $userId = null): Response
    {
        /**
         * @var $currentUser User
         */
        $currentUser = $this->getUser();

        /**
         * @var $collabUser User
         */
        $collabUser = $users->findOneBy([
            'id' => $userId,
        ]);

        // Get start and end of week
        $startOfWeek = new \DateTime('sunday -1 week');
        $endOfWeek = new \DateTime('sunday');
        $currentWeekOfficeDatesCollab = $officeRepository->getOfficeDateBetweenDate($startOfWeek, $endOfWeek, $collabUser->getId(), false);

        $userNextMeps = $mepRepository->getNextMepUser($collabUser->getId());

        $allNotes = $notes->findBy([
            'collab' => $collabUser,
            'author' => $currentUser,
            'type' => Note::TYPE_ONETOONE,
        ], ['publishedAt' => 'DESC',], 3);

        $line = new LineChart();
        $graphData[] = [['label' => 'x', 'type' => 'datetime'], ['label' => 'values', 'type' => 'number']];

        /**
         * @var $note Note
         */
        foreach ($allNotes as $note) {
            if ($note->getMindset() != null) {
                $graphData[] = [$note->getPublishedAt(), $note->getMindset()->getValue()];
            }
        }

        $showMindsetHistory = ($currentUser === $collabUser) || ($currentUser === $collabUser->getManager());

        /**
         * @var $mindset MindsetDTO
         */
        $mindset = new MindsetDTO(0, 0);
        if ($showMindsetHistory) {
            $mindset = $mindsetRepository->getMindset($collabUser->getId());
        }

        $line->getData()->setArrayToDataTable($graphData);
        $line->getOptions()
            ->setCurveType('function')
            ->setLineWidth(5)
            ->setHeight(300)
            ->getLegend()->setPosition('none');

        $jiraResults = $this->getUserIssues($collabUser, $currentUser);

        $showAlerts = ($currentUser === $collabUser);

        $allUsersN1 = $users->getUsersForManager($collabUser->getId());

        $mindsets = [];
        $squadList = [];

        /**
         * @var User $userN1
         */
        foreach ($allUsersN1 as $userN1) {
            if ($showMindsetHistory) {
                /**
                 * @var $mindset MindsetDTO
                 */
                $mindset = $mindsetRepository->getMindset($userN1->getId(),null);
                $mindsets += [$userN1->getId() => $mindset];
            }

            if ($userN1->getSquad() != null) {
                array_push($squadList, $userN1->getSquad());
            }
        }

        $squadList = array_unique($squadList);

        $mindsetsSquad = [];
        if ($showMindsetHistory) {
            foreach ($squadList as $squad) {
                /**
                 * @var $mindset MindsetDTO
                 */
                $mindsetSquad = $mindsetRepository->getMindsetSquad($squad->getId());
                $mindsetsSquad += [$squad->getId() => $mindsetSquad];
            }
        }

        $o3List = $o3Repository->findBy(['collaborator' => $collabUser]);

        return new Response(
            $this->renderView('collab/collab.html.twig', [
                'collab' => $collabUser,
                'showMindsetHistory' => $showMindsetHistory,
                'allNotes' => $allNotes,
                'linechart' => $line,
                'mindset' => $mindset,
                'jiraResults' => $jiraResults,
                'currentWeekOfficeDatesCollab' => $currentWeekOfficeDatesCollab,
                'showAlerts' => $showAlerts,
                'nextMepDates' => $userNextMeps,
                'allUsersN1' => $allUsersN1,
                'mindsets' => $mindsets,
                'squads' => $squadList,
                'mindsetsSquad' => $mindsetsSquad,
                'o3List' => $o3List
            ]),
            200,
            [
                //'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    private function getUserIssues($collabUser, $currentUser): ?\Unirest\Response
    {
        if ($collabUser->getIdjira() != null && $currentUser->getApikeyjira() != null) {
            Unirest\Request::auth($currentUser->getEmail(), $currentUser->getApikeyjira());

            $headers = array(
                'Accept' => 'application/json'
            );

            $query = array(
                'jql' => 'assignee in (' . $collabUser->getIdjira() . ') AND updated >= -2w AND project in (BLL, BLLCMS) AND statusCategory in (2, 4) ORDER BY cf[17236] ASC, status DESC, created DESC'
            );

            try{
                return Unirest\Request::get(
                    'https://xxxxxxxx.atlassian.net/rest/api/2/search',
                    $headers,
                    $query
                );
            }catch(Exception $exception){

            }

        }
        return null;
    }

    #[Route("/mindset/history/{userId}",name: "collab_mindset_history")]
    public function getMindsetHistory(int $userId, MindsetRepository $mindsetRepository): Response
    {
        $result = $mindsetRepository->getMindsetHistory($userId);

        return $this->render('mindset/mindsetHistory.json.twig', [
            'mindsetHistory' => $result,
        ]);
    }

    #[Route("/mep/confirm/{mepId}",name: "confirm_mep")]
    public function confirmMep(Request $request, MepRepository $mepRepository, ManagerRegistry $managerRegistry, int $mepId): Response
    {
        /**
         * @var Mep $selectMep
         */
        $selectMep = $mepRepository->find($mepId);

        if ($selectMep->getState() == Mep::STATE_TOCONFIRM) {
            $selectMep->setState(Mep::STATE_CONFIRM);
        } elseif ($selectMep->getState() == Mep::STATE_CONFIRM) {
            $selectMep->setState(Mep::STATE_TOCONFIRM);
        } else {
            return new JsonResponse(["error" => "Bad param, use checked or unchecked"], Response::HTTP_BAD_REQUEST);
        }


        $em = $managerRegistry->getManagerForClass(Mep::class);
        $em->persist($selectMep);
        $em->flush();

        return new Response($selectMep->getState(), Response::HTTP_OK);
    }




}
