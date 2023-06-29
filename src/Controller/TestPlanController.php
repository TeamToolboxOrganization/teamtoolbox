<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\CSPDefinition;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Unirest;

/**
 * Controller used to manage Test Plan datas
 */
#[IsGranted('ROLE_USER')]
#[Route("/testplan")]
class TestPlanController extends AbstractController
{

    #[Route("/plans",name: "get_plans_list")]
    public function getPlansList(): Response
    {
        $testPlanURL = $this->getParameter('test_plan_url');
        $plans = [];

        if(!empty($testPlanURL)){

            /**
             * @var User $user
             */
            $user = $this->getUser();

            Unirest\Request::auth($user->getEmail(), $user->getApikeyazdo());

            $headers = array(
                'Accept' => 'application/json'
            );

            $result = Unirest\Request::get(
                $testPlanURL . '/_apis/testplan/plans?api-version=7.0',
                $headers
            );

            $plans = $result->body->value;
        }

        return new Response(
            $this->renderView('testplan/plansList.html.twig', [
                'plans' => $plans,
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    #[Route("/plans/{planId}/suitestests",name: "get_suites_tests")]
    public function getSuitesTests(string $planId): Response
    {

        $testPlanURL = $this->getParameter('test_plan_url');
        $suites = [];

        if(!empty($testPlanURL)) {

            /**
             * @var User $user
             */
            $user = $this->getUser();

            Unirest\Request::auth($user->getEmail(), $user->getApikeyazdo());

            $headers = array(
                'Accept' => 'application/json'
            );

            $result = Unirest\Request::get(
                $testPlanURL . '/_apis/testplan/Plans/' . $planId . '/suites?api-version=7.0&asTreeView=true',
                $headers
            );

            $suites = $result->body->value;
        }

        return new Response(
            $this->renderView('testplan/suitesTests.html.twig', [
                'suites' => $suites,
                'planId' => $planId,
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    #[Route("/plans/{planId}/suitetest/{suiteId}/testscases",name: "get_tests_cases")]
    public function getTestCases(string $planId, string $suiteId): Response
    {
        $testPlanURL = $this->getParameter('test_plan_url');
        $testsCases = [];

        if(!empty($testPlanURL)) {

            /**
             * @var User $user
             */
            $user = $this->getUser();

            Unirest\Request::auth($user->getEmail(), $user->getApikeyazdo());

            $headers = array(
                'Accept' => 'application/json'
            );

            $result = Unirest\Request::get(
                $testPlanURL . '/_apis/testplan/Plans/' . $planId . '/Suites/' . $suiteId . '/TestCase?api-version=7.0',
                $headers
            );

            $testsCases = $result->body->value;
        }

        return new Response(
            $this->renderView('testplan/testsCases.html.twig', [
                'testscases' => $testsCases,
                'planId' => $planId,
                'suiteId' => $suiteId,
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

}
