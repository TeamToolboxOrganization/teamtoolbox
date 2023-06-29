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

use App\Entity\GanttTask;
use App\Entity\GanttLink;
use App\Repository\GanttTaskRepository;
use App\Repository\GanttLinkRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Unirest;


#[Route("/gantt/control")]
class GanttUpdateController extends AbstractController
{
    #[Route("/task",name: "gantt_create_task",methods: ["POST"])]
    public function createTask(Request $request, GanttTaskRepository $tasks, UserRepository $users, ManagerRegistry $managerRegistry): Response
    {
        $task = new GanttTask();

        $task->setStartDate(new \DateTime($request->get("start_date")));
        $task->setText($request->get("text"));
        $task->setDuration($request->get("duration"));
        $task->setProgress($request->get("progress"));
        $task->setType($request->get("type"));

        $newDeadline = $request->get("deadline");
        if($newDeadline == null){
            $task->setDeadline(null);
        } else {
            $task->setDeadline(new \DateTime($request->get("deadline")));
        }

        $newSquad = $request->get("squad");
        if($newSquad){
            $task->setSquad($newSquad);
        }

        $newOwnerId = $request->get("owner");
        if($newOwnerId){
            $newOwner = $users->find($newOwnerId);
            $task->setOwner($newOwner);
        }

        $maxSortOrder = $tasks->getMaxOrder();
        $task->setSortOrder($maxSortOrder+1);

        if($task->getType() == GanttTask::TYPE_MILESTONE){
            $task->setDuration(0);
            $task->setProgress(0);
        } else {
            $task->setDuration($request->get("duration"));
            $task->setProgress($request->get("progress"));
        }

        $parent = $tasks->find($request->get("parent"));
        if($parent !== null){
            $task->setParent($parent);
        }

        $em = $managerRegistry->getManagerForClass(GanttTask::class);
        $em->persist($task);
        $em->flush();

        return new JsonResponse([
            "action" => "inserted",
            "tid" => $task->getId(),
            "tmpid" => $request->get("id"),
            "type" => "task",
        ], Response::HTTP_CREATED);
    }

    #[Route("/task/{taskId}",name: "gantt_update_task",methods: ["POST"])]
    public function updateTask(Request $request, int $taskId = null, GanttTaskRepository $tasks, UserRepository $users, ManagerRegistry $managerRegistry): Response
    {
        /**
         * @var $task GanttTask
         */
        $task = $tasks->find($taskId);
        $task->setStartDate(new \DateTime($request->get("start_date")));
        $task->setEndDate(new \DateTime($request->get("end_date")));
        $task->setText($request->get("text"));
        $task->setType($request->get("type"));

        $newDeadline = $request->get("deadline");
        if($newDeadline == null){
            $task->setDeadline(null);
        } else {
            $task->setDeadline(new \DateTime($request->get("deadline")));
        }


        $newSquad = $request->get("squad");
        if($newSquad){
            $task->setSquad($newSquad);
        }

        $newOwnerId = $request->get("owner");
        if($newOwnerId){
            $newOwner = $users->find($newOwnerId);
            $task->setOwner($newOwner);
        }

        if($task->getType() == GanttTask::TYPE_MILESTONE){
            $task->setDuration(0);
            $task->setProgress(0);
        } else {
            $task->setDuration($request->get("duration"));
            $task->setProgress($request->get("progress"));
        }

        /**
         * @var $parent GanttTask
         */
        $parent = $tasks->find($request->get("parent"));
        if($parent !== null){
            $task->setParent($parent);
            // Check if we must update parent task
            // TODO : continue here to calcul parent US
            if($parent->getJiraType() == GanttTask::JIRA_TYPE_US){
                $tasks->calculatePlanFromChildren($parent);
                $em = $managerRegistry->getManagerForClass(GanttTask::class);
                $em->persist($parent);
            }

        }

        $targetForOrder = $request->get("target");
        if($targetForOrder){
            $task->setSortOrder($this->updateOrder($targetForOrder, $tasks));
        }

        $em = $managerRegistry->getManagerForClass(GanttTask::class);
        $em->persist($task);
        $em->flush();

        return new JsonResponse([
            "action" => "updated",
        ], Response::HTTP_OK);
    }

    /**
     * @param $target
     * @param GanttTaskRepository $tasks
     * @return int|void|null
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function updateOrder(string $target, GanttTaskRepository $tasks): ?int{
        $nextTask = false;
        $targetId = $target;

        if(strpos($target, "next:") === 0){
            $targetId = substr($target, strlen("next:"));
            $nextTask = true;
        }

        if($targetId == "null"){
            return null;
        }

        /**
         * @var $taskForOrder GanttTask
         */
        $taskForOrder = $tasks->find($targetId);
        $targetOrder = $taskForOrder->getSortOrder();

        if($nextTask){
            $targetOrder = $targetOrder + 1;
        }

        $tasks->updateOrder($targetOrder);

        return $targetOrder;
    }

    #[Route("/task/delete/{taskId}",name: "gantt_delete_task",methods: ["POST"])]
    public function deleteTask(Request $request, int $taskId = null, GanttTaskRepository $tasks, ManagerRegistry $managerRegistry): Response
    {
        $task = $tasks->find($taskId);
        $em = $managerRegistry->getManagerForClass(GanttTask::class);
        $em->remove($task);
        $em->flush();

        return new JsonResponse([
            "action" => "deleted",
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/link", methods="POST", name="gantt_create_link")
     * Cache(smaxage="10")
     */
    public function createLink(Request $request, GanttTaskRepository $tasks, GanttLinkRepository $links, ManagerRegistry $managerRegistry): Response
    {
        $link = new GanttLink();

        $link->setSource($tasks->find($request->get("source")));
        $link->setTarget($tasks->find($request->get("target")));
        $link->setType($request->get("type"));

        $em = $managerRegistry->getManagerForClass(GanttLink::class);
        $em->persist($link);
        $em->flush();

        return new JsonResponse([
            "action" => "inserted",
            "tid" => $link->getId(),
            "tmpid" => $request->get("id"),
            "type" => "link",
        ], Response::HTTP_CREATED);
    }

    #[Route("/link/delete/{linkId}",name: "gantt_delete_link",methods: ["POST"])]
    public function deleteLink(Request $request, int $linkId = null, GanttLinkRepository $links, ManagerRegistry $managerRegistry): Response
    {
        $link = $links->find($linkId);
        $em = $managerRegistry->getManagerForClass(GanttLink::class);
        $em->remove($link);
        $em->flush();

        return new JsonResponse([
            "action" => "deleted",
        ], Response::HTTP_OK);
    }
}
