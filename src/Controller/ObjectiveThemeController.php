<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ObjectiveThemeRepository;
use App\Security\CSPDefinition;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Unirest;

/**
 *
 * @Route("/objectives")
 *
 */
#[IsGranted('ROLE_USER')]
#[Route("/objectives")]
class ObjectiveThemeController extends AbstractController
{
    #[Route("/",name: "objectives_index", methods: ["GET"])]
    public function index(ObjectiveThemeRepository $objectiveThemes): Response
    {
        $allObjectiveThemes = $objectiveThemes->findBy([
        ], ['progress' => 'DESC',], 20);

        return new Response(
            $this->renderView('objectives/objectives.html.twig', [
                'objectiveThemes' => $allObjectiveThemes,
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

}
