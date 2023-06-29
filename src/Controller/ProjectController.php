<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\User;
use App\Form\SearchProductType;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Security\CSPDefinition;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_SCREEN')]
#[Route("/project")]
class ProjectController extends AbstractController {

    #[Route("/list",name: "project_list", methods: ["GET"])]
    public function projectList(ProjectRepository $projectRepository): Response {

        $projectList = $projectRepository->findAll();

        return $this->render('project/list.html.twig', [
            'projects' => $projectList,
        ]);
        //
    }

    #[Route("/detail/{id}",name: "project_details", methods: ["GET"])]
    #[ParamConverter('id', class: 'App\Entity\Project')]
    public function projectDetails(Project $project): Response {

        $userList = $project->getUsers();

        return $this->render('project/details.html.twig', [
            'project' => $project,
            'users' => $userList,
        ]);
        //
    }

    #[Route("/search",name: "project_search", methods: ["GET","POST"])]
    public function projectSearch(Request $request, ProjectRepository $projectRepository): Response {

        $form = $this->createForm(SearchProductType::class);
        $form->handleRequest($request);

        $projects = $projectRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->get('criteria');
            $projects = $projectRepository->searchProject($criteria->getData());
        }

        return new Response(
            $this->renderView('project/search.html.twig', [
                'form' => $form->createView(),
                'projects' => $projects,
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy-Report-Only' => CSPDefinition::defaultRules
            ]
        );
    }

}
