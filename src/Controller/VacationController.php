<?php

namespace App\Controller;

use App\Entity\Vacation;
use App\Form\VacationType;
use App\Repository\VacationRepository;
use App\Security\CSPDefinition;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('ROLE_USER')]
#[Route('/vacation')]
class VacationController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route("/{userId}/requestVacation/",name: "vacation_date_request")]
    public function requestVacation(Request $request, int $userId, ManagerRegistry $managerRegistry, VacationRepository $vacationRepository) : Response
    {
        $currentUserId = $this->getUser()->getId();
        if($currentUserId === $userId){
            $vacation = new Vacation();
            $vacation->setState("En cours de validation");
            $vacation->setCollab($this->getUser());

            $form = $this->createForm(VacationType::class, $vacation);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                $vm = $managerRegistry->getManagerForClass(Vacation::class);
                $vm->persist($vacation);
                $vm->flush();

                $this->addFlash('success', "vacation.requestVacation.successMessage");
            }

            return new Response(
                $this->renderView('vacation/new.html.twig', [
                    'vacation' => $vacation,
                    'form' => $form->createView(),
                    'userId' => $userId,
                    'vacationsLeft' => $vacationRepository->getVacationsLeft($currentUserId) // Solde de congés restants
                ]),
                Response::HTTP_OK,
                [
                    'Content-Security-Policy-Report-Only' => CSPDefinition::defaultRules
                ]
            );
        }
        else{
            return $this->redirectToRoute('collab_index', ['userId' => $currentUserId]);
        }

    }

    #[isGranted('ROLE_MANAGER')]
    #[Route("/changeVacationStatus/{vacationId}",name: "vacation_date_validation", methods: ['POST'])]
    public function changeVacationStatus(Request $request, ManagerRegistry $managerRegistry, VacationRepository $vacationRepository, int $vacationId, TranslatorInterface $translator) : JsonResponse
    {
        $vacation = $vacationRepository->find($vacationId);

        if($this->getUser()->getId() === $vacation->getCollab()->getManager()->getId()){
            $newStatus = $request->get('newStatus');

            if($newStatus == 1){
                // Mise à jour du statut "Accepté"
                $vacation->setState($vacationRepository->stateOK);
                $state_class = 'state_ok';
                $current_state_name = new TranslatableMessage('vacation.status.OK');
            }
            if($newStatus == 2){
                // Mise à jour du statut "Refusé"
                $vacation->setState($vacationRepository->stateNotOk);
                $state_class = 'state_not_ok';
                $current_state_name = new TranslatableMessage('vacation.status.KO');
            }

            $em = $managerRegistry->getManager();
            $em->persist($vacation);
            $em->flush();

            return new JsonResponse(['stateClass' => $state_class, 'currentStateName' => $current_state_name->trans($translator)], Response::HTTP_OK);
        }
        else{
            $error_message = new TranslatableMessage('vacation.error.forbidden');
            return new JsonResponse(['error' => $error_message->trans($translator)], Response::HTTP_FORBIDDEN);
        }
    }

    #[isGranted('ROLE_MANAGER')]
    #[Route("/showVacationsList", name: "vacation_show_list")]
    public function showVacationsList(Request $request, ManagerRegistry $managerRegistry, VacationRepository $vacationRepository) : Response
    {
        $vacations = $vacationRepository->getVacations($this->getUser()->getId());
        return new Response(
            $this->renderView('vacation/vacationsList.html.twig', [
                'vacations' => $vacations,
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy-Report-Only' => CSPDefinition::defaultRules
            ]
        );

    }
}