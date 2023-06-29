<?php

namespace App\Controller;

use App\Entity\Mep;
use App\Entity\User;
use App\Entity\UserDate;
use App\Form\MepType;
use App\Repository\MepRepository;
use App\Security\CSPDefinition;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_SCREEN')]
#[Route("/map")]
class MepController extends AbstractController
{

    #[Route("/list",name: "mep_index")]
    public function index(MepRepository $mepRepository): Response
    {
        $meps = $mepRepository->findBy(
            [

            ],
            [
                'startAt' => 'ASC'
            ]
        );

        return new Response(
            $this->renderView('mep/listMep.html.twig', [
                'meps' => $meps,
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    #[IsGranted('ROLE_MEP_ORGA')]
    #[Route("/new",name: "mep_new")]
    public function new(Request $request, ManagerRegistry $managerRegistry): Response
    {
        $currentDate = new \DateTime();
        $mep = new Mep();
        $mep->setStartAt($currentDate);
        $mep->setType(UserDate::TYPE_MEP);
        $mep->setState(Mep::STATE_TOCONFIRM);

        $form = $this->createForm(MepType::class, $mep);
        $form->handleRequest($request);


        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See https://symfony.com/doc/current/forms.html#processing-forms
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $managerRegistry->getManagerForClass(Mep::class);
            $em->persist($mep);
            $em->flush();

            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.
            // See https://symfony.com/doc/current/controller.html#flash-messages
            $this->addFlash('success', 'Création réussie');

            return $this->redirectToRoute('mep_index');
        }

        return new Response(
            $this->renderView('mep/edit.html.twig', [
                'mep' => $mep,
                'form' => $form->createView(),
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    #[IsGranted('ROLE_MEP_ORGA')]
    #[Route("/edit/{mepId}",name: "mep_edit")]
    public function edit(Request $request, MepRepository $mepRepository, int $mepId, ManagerRegistry $managerRegistry): Response
    {

        $mep = $mepRepository->find($mepId);
        $mep->setType(UserDate::TYPE_MEP);
        $form = $this->createForm(MepType::class, $mep);
        $form->handleRequest($request);


        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See https://symfony.com/doc/current/forms.html#processing-forms
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $managerRegistry->getManagerForClass(Mep::class);
            $em->persist($mep);
            $em->flush();

            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.
            // See https://symfony.com/doc/current/controller.html#flash-messages
            $this->addFlash('success', 'Mise à jour réussie');

            return $this->redirectToRoute('mep_index');
        }

        return new Response(
            $this->renderView('mep/edit.html.twig', [
                'mep' => $mep,
                'form' => $form->createView(),
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    #[IsGranted('ROLE_MEP_ORGA')]
    #[Route("/delete/{mepId}",name: "mep_delete")]
    public function delete(MepRepository $mepRepository, int $mepId, ManagerRegistry $managerRegistry): Response
    {
        /**
         * @var UserDate $userDate
         */
        $mep = $mepRepository->find($mepId);

        $em = $managerRegistry->getManagerForClass(UserDate::class);
        $em->remove($mep);
        $em->flush();

        return new Response($mepId, Response::HTTP_OK);
    }

    #[IsGranted('ROLE_MEP_ORGA')]
    #[Route("/deleteInteractive/{mepId}",name: "mep_delete_interactive")]
    public function deleteInteractive(MepRepository $mepRepository, int $mepId, ManagerRegistry $managerRegistry): Response
    {
        /**
         * @var UserDate $userDate
         */
        $mep = $mepRepository->find($mepId);

        $em = $managerRegistry->getManagerForClass(UserDate::class);
        $em->remove($mep);
        $em->flush();

        $this->addFlash('success', 'Mep supprimée');
        return $this->redirectToRoute('mep_index');
    }

}
