<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\User;
use App\Repository\GanttTaskRepository;
use App\Repository\GanttLinkRepository;
use App\Repository\UserRepository;
use App\Security\CSPDefinition;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Unirest;

#[Route("/gantt")]
class GanttController extends AbstractController
{
    #[Route("/",name: "gantt_index")]
    public function index(Request $request, Security $security, GanttTaskRepository $tasks): Response
    {
        /**
         * @var $currentUser User
         */
        $currentUser = $this->getUser();

        $epicId = $request->get('epicId');
        $epic = null;
        if ($epicId != null) {
            $epic = $tasks->find($epicId);
        }


        if (($security->isGranted('ROLE_LT') && !$security->isGranted('ROLE_MANAGER')) || $security->isGranted('ROLE_ADMIN')) {
            return new Response(
                $this->renderView('gantt/index.html.twig', [
                    'viewType' => $request->get('view'),
                    'epic' => $epic,
                    'squad' => $currentUser->getSquad(),
                ]),
                Response::HTTP_OK,
                [
                    //'Content-Security-Policy-Report-Only' => CSPDefinition::defaultRules
                ]
            );
        }

        return new Response(
            $this->renderView('gantt/index.html.twig', [
                'viewType' => 'view',
                'squad' => $currentUser->getSquad(),
            ]),
            Response::HTTP_OK,
            [
                //'Content-Security-Policy-Report-Only' => CSPDefinition::defaultRules
            ]
        );
    }

    #[Route("/holidays/",name: "gantt_holidays")]
    public function getHolidays(): Response
    {
        $gouvUser = $this->getParameter('gouv_api_user');
        $gouvKey = $this->getParameter('gouv_api_key');

        $datas = [];

        if($gouvUser == null || $gouvKey == null){
            return JSONResponse::fromJsonString(json_encode($datas));
        }

        // This code sample uses the 'Unirest' library:
        // http://unirest.io/php.html
        Unirest\Request::auth($gouvUser, $gouvKey);

        $headers = array(
            'Accept' => 'application/json'
        );

        try {
            $responseData = Unirest\Request::get(
                'https://calendrier.api.gouv.fr/jours-feries/metropole/' . date("Y") . '.json',
                $headers,
            );

            foreach ($responseData->body as $value => $label) {
                $datas[] = ["value" => $value, "label" => $label];
            }

        } catch (Exception $exception) {

        }

        return JSONResponse::fromJsonString(json_encode($datas));
    }

    #[Route("/data/",name: "gantt_data")]
    public function getData(Request $request, GanttTaskRepository $tasks, GanttLinkRepository $links, UserRepository $users, Security $security): Response
    {
        /**
         * @var $currentUser User
         */
        $currentUser = $this->getUser();

        if ($security->isGranted('ROLE_MANAGER')) {
            $allUsers = $users->getUsersForManager($currentUser->getId());
        } elseif ($security->isGranted('ROLE_LT')) {
            $allUsers = $users->getUsersForSquad($currentUser->getSquad()->getId());
        } else {
            $allUsers = null;
        }

        $epicId = $request->get('epicId');

        if ($epicId) {
            $allTasks = $tasks->getTasksFromEpic($epicId);
            $allLinks = $links->getLinksFromTasks($allTasks);
        } else {
            $allTasks = $tasks->findBy([], ['sortOrder' => 'ASC',]);
            $allLinks = $links->findAll();
        }

        return $this->render('gantt/ganttData.html.twig', [
            'allTasks' => $allTasks,
            'allLinks' => $allLinks,
            'allUsers' => $allUsers,
            'open' => $request->get('open'),
        ]);
    }

    #[Route("/users/",name: "gantt_users")]
    public function users(UserRepository $users, Security $security): Response
    {
        /**
         * @var $currentUser User
         */
        $currentUser = $this->getUser();

        if ($security->isGranted('ROLE_MANAGER')) {
            $allUsers = $users->getUsersForManager($currentUser->getId());
        } elseif ($security->isGranted('ROLE_LT')) {
            $allUsers = $users->getUsersForSquad($currentUser->getSquad()->getId());
        }

        return $this->render('gantt/ganttUsers.json.twig', [
            'allUsers' => $allUsers,
        ]);
    }
}
