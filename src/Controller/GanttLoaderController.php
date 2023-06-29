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

use App\Entity\Configuration;
use App\Entity\GanttTask;
use App\Entity\User;
use App\Repository\ConfigurationRepository;
use App\Repository\GanttTaskRepository;
use App\Repository\GanttLinkRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Unirest;

/**
 *
 * @Route("/gantt/loader")
 *
 */
#[IsGranted('ROLE_ADMIN')]
#[Route("/gantt/loader")]
class GanttLoaderController extends AbstractController
{

    #[Route("/jira/",name: "gantt_jira_data")]
    public function loadJiraData(Request $request, ManagerRegistry $managerRegistry, ConfigurationRepository $configurationRepository, GanttTaskRepository $tasks, GanttLinkRepository $links, UserRepository $users, Security $security): Response
    {

        /**
         * @var $currentUser User
         */
        $currentUser = $this->getUser();
        $jiraUrl = $configurationRepository->findOneBy(['name' => 'jira_url'])->getValue();

        $response = null;
        if($currentUser->getIdjira() != null && $currentUser->getApikeyjira() != null){
            // This code sample uses the 'Unirest' library:
            // http://unirest.io/php.html
            Unirest\Request::auth($currentUser->getEmail(), $currentUser->getApikeyjira());

            $headers = array(
                'Accept' => 'application/json'
            );

            $query = array(
                'jql' => 'labels = TeamToolbox order by created DESC'
            );

            $response = Unirest\Request::get(
                $jiraUrl . 'rest/api/2/search',
                $headers,
                $query
            );

            foreach ($response->body->issues as $issue){
                if($issue->fields->issuetype->id == "10100"){
                    $this->loadEpicFromJira($managerRegistry, $issue->key, $currentUser->getEmail(), $currentUser->getApikeyjira(), $tasks, $users);
                } else {
                    $this->loadIssueFromJira($managerRegistry, $issue,null, $tasks, $users, $currentUser->getEmail(), $currentUser->getApikeyjira());
                }
            }
        }

        return new JsonResponse(["action" => "updated"], Response::HTTP_OK);
    }

    private function loadEpicFromJira(ManagerRegistry $managerRegistry, string $epicKey, string $userMail, string $userApiKeyJira, GanttTaskRepository $tasks, UserRepository $users){
        $response = $this->getEpicData($userMail, $userApiKeyJira, $epicKey);

        $epic = $tasks->findOneBy(['key' => $response->body->key]);

        if (!$epic) {
            $epic = new GanttTask();
            $epic->setKey($response->body->key);
            $epic->setProgress(0);
            $maxSortOrder = $tasks->getMaxOrder();
            $epic->setSortOrder($maxSortOrder + 1);
        }

        $this->setTaskSquad($epic, $response->body);

        $epic->setText(str_replace('"', '', $response->body->fields->summary));
        $epic->setDuration(5);
        $epic->setType(GanttTask::TYPE_PROJECT);
        $epic->setJiraType(GanttTask::JIRA_TYPE_EPIC);

        $em = $managerRegistry->getManagerForClass(GanttTask::class);
        $em->persist($epic);
        $em->flush();

        // Convert issues linked
        $response = $this->getEpicLinks($userMail, $userApiKeyJira, $epicKey);
        foreach ($response->body->issues as $issue) {
            $this->loadIssueFromJira($managerRegistry, $issue, $epic, $tasks, $users, $userMail, $userApiKeyJira);
        }
    }

    private function getEpicLinks(string $mail, string $apiKey, string $epicKey) : Unirest\Response{
        $jiraUrl = $this->getParameter('jira_url');
        $response = null;
        if($apiKey != null && $jiraUrl != null){
            // This code sample uses the 'Unirest' library:
            // http://unirest.io/php.html
            Unirest\Request::auth($mail, $apiKey);

            $headers = array(
                'Accept' => 'application/json'
            );

            $response = Unirest\Request::get(
                $jiraUrl . '/rest/agile/1.0/epic/' . $epicKey . '/issue?fields=issuelinks,issuetype,customfield_17214,timeestimate,summary,assignee,customfield_11101',
                $headers,
            );
        }

        return $response;
    }

    private function getEpicData(string $mail, string $apiKey, string $epicKey) : Unirest\Response{
        $jiraUrl = $this->getParameter('jira_url');
        $response = null;
        if($apiKey != null && $jiraUrl != null){
            // This code sample uses the 'Unirest' library:
            // http://unirest.io/php.html
            Unirest\Request::auth($mail, $apiKey);

            $headers = array(
                'Accept' => 'application/json'
            );

            $response = Unirest\Request::get(
                $jiraUrl . '/rest/api/3/issue/' . $epicKey,
                $headers,
            );
        }

        return $response;
    }

    private function getJiraData(string $mail, string $apiKey, string $url) : Unirest\Response{
        $response = null;
        if($apiKey != null){
            // This code sample uses the 'Unirest' library:
            // http://unirest.io/php.html
            Unirest\Request::auth($mail, $apiKey);

            $headers = array(
                'Accept' => 'application/json'
            );

            $response = Unirest\Request::get(
                $url,
                $headers,
            );
        }

        return $response;
    }

    private function loadIssueFromJira(ManagerRegistry $managerRegistry, object $issue, ?GanttTask $parentTask, GanttTaskRepository $tasks, UserRepository $users, string $mail, string $apiKey): GanttTask
    {
        $task = $tasks->findOneBy(['key' => $issue->key]);

        if (!$task) {
            $task = new GanttTask();
            $task->setKey($issue->key);
            $maxSortOrder = $tasks->getMaxOrder();
            $maxSortOrder = $maxSortOrder + 1;
            $task->setSortOrder($maxSortOrder);
            $task->setProgress(0);
            if($parentTask){
                $task->setParent($parentTask);
            }
        }

        if ($issue->fields->issuetype->id == "10200") {
            $task->setJiraType(GanttTask::JIRA_TYPE_US);
        } elseif ($issue->fields->issuetype->id == "10101" || $issue->fields->issuetype->id == "10102") {
            $task->setJiraType(GanttTask::JIRA_TYPE_TASK);
        } elseif ($issue->fields->issuetype->id == "10500") {
            $task->setJiraType(GanttTask::JIRA_TYPE_SPIKE);
        } elseif ($issue->fields->issuetype->id == "1") {
            $task->setJiraType(GanttTask::JIRA_TYPE_BUG);
        } elseif ($issue->fields->issuetype->id == "11400") {
            $task->setJiraType(GanttTask::JIRA_TYPE_DEFECT);
        } else {
            $task->setJiraType($issue->fields->issuetype->name);
        }

        $task->setText(str_replace(['"', "\n"], ['', ' '], $issue->fields->summary));

        if(property_exists($issue->fields, "customfield_17214")){
            $task->setStartDate(new \DateTime($issue->fields->customfield_17214));
        }

        $this->setTaskSquad($task, $issue);

        if (property_exists($issue->fields, "timeestimate") && $issue->fields->timeestimate) {
            $task->setDuration($issue->fields->timeestimate / (3600 * 8));
        } else {
            $task->setDuration(1);
        }

        if (property_exists($issue->fields, "assignee") && $issue->fields->assignee) {
            $owner = $users->findOneBy([
                'idjira' => $issue->fields->assignee->accountId,
            ]);
            $task->setOwner($owner);
        }

        $em = $managerRegistry->getManagerForClass(GanttTask::class);
        $em->persist($task);
        $em->flush();

        if (property_exists($issue->fields, "issuelinks") && $issue->fields->issuelinks) {
            foreach ($issue->fields->issuelinks as $link) {
                if (property_exists($link, "outwardIssue")) {
                    $response = $this->getJiraData($mail, $apiKey, $link->outwardIssue->self);
                    //$this->convertIssueToGanttItem($response->body, $maxSortOrder, $task, $tasks, $users, $mail, $apiKey);
                    $linkTask = $this->loadIssueFromJira($managerRegistry, $link->outwardIssue, $task, $tasks, $users, $mail, $apiKey);
                    $this->setTaskSquad($linkTask, $response->body);
                    $em->persist($linkTask);
                    $em->flush();
                }
                if (property_exists($link, "inwardIssue")) {
                    $response = $this->getJiraData($mail, $apiKey, $link->inwardIssue->self);
                    //$this->convertIssueToGanttItem($response->body, $maxSortOrder, $task, $tasks, $users,$mail, $apiKey);
                    $linkTask = $this->loadIssueFromJira($managerRegistry, $link->inwardIssue, $task, $tasks, $users, $mail, $apiKey);
                    $this->setTaskSquad($linkTask, $response->body);
                    $em->persist($linkTask);
                    $em->flush();
                }
            }
        }

        return $task;
    }

    private function setTaskSquad(GanttTask $task, \stdClass $jiraResponse): void{
        if (property_exists($jiraResponse->fields, "customfield_11101")) {
            if ($jiraResponse->fields->customfield_11101->id == "18601") {
                $task->setSquad("18601");
            } else if ($jiraResponse->fields->customfield_11101->id == "18600") {
                $task->setSquad("18600");
            } else {
                $task->setSquad("N/A");
            }
        } else {
            if($task->getSquad() == null){
                $task->setSquad("N/A");
            }
        }
    }
}
