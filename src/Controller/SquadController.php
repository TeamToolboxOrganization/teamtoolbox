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

use App\Entity\MindsetDTO;
use App\Entity\Squad;
use App\Entity\User;
use App\Form\SquadType;
use App\Repository\MindsetRepository;
use App\Repository\SquadRepository;
use App\Security\CSPDefinition;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @Route("/squad")
 *
 */
#[IsGranted('ROLE_USER')]
#[Route("/squad")]
class SquadController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route("/new",name: "squad_new", methods: ["GET","POST"])]
    public function new(Request $request, SluggerInterface $slugger, ManagerRegistry $managerRegistry): Response
    {
        $squad = new Squad();

        $form = $this->createForm(SquadType::class, $squad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $managerRegistry->getManagerForClass(Squad::class);
            $em->persist($squad);
            $em->flush();

            $this->addFlash('success', 'Squad ' . $squad->getName() . ' créée');

            return $this->redirectToRoute('squad_index');
        }

        return new Response(
            $this->renderView('squad/edit.html.twig', [
                'squad' => $squad,
                'form' => $form->createView(),
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    #[Route("/",name: "squad_index", methods: ["GET"])]
    #[Route("/{squadId}",name: "squad_index", methods: ["GET"])]
    public function index(UserRepository $users, MindsetRepository $mindsetRepository, SquadRepository $squads, Security $security, int $squadId = null): Response
    {
        $isManager = $security->isGranted('ROLE_MANAGER');
        $isDetailPage = ($squadId != null);

        /**
         * @var $currentUser User
         */
        $currentUser = $this->getUser();

        if ($isDetailPage) {
            return $this->generateDetailsPage($squads, $mindsetRepository, $squadId, $currentUser, $isManager);
        } else {
            return $this->generateListPage($squads, $mindsetRepository, $isManager);
        }
    }

    private function generateDetailsPage(SquadRepository $squads, MindsetRepository $mindsetRepository, ?int $squadId, User $currentUser, bool $isManager): Response
    {
        /**
         * @var Squad $searchSquad
         */
        $searchSquad = $squads->find($squadId);
        $resultUsers = $searchSquad->getUsers();

        $mindsets = [];
        /**
         * @var $collab User
         */
        foreach ($resultUsers as $collab) {
            if ($collab->getManager() == $this->getUser()) {
                /**
                 * @var $mindset MindsetDTO
                 */
                $mindset = $mindsetRepository->getMindset($collab->getId(), $currentUser->getId());
                $mindsets += [$collab->getId() => $mindset];
            }
        }

        $mindsetSquad = new MindsetDTO(0, 0);
        if ($isManager) {
            $mindsetSquad = $mindsetRepository->getMindsetSquad($squadId);
        }

        return new Response(
            $this->renderView('squad/squad.html.twig', [
                'users' => $resultUsers,
                'mindsets' => $mindsets,
                'mindset' => $mindsetSquad,
                'squad' => $searchSquad
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    private function generateListPage(SquadRepository $squads, MindsetRepository $mindsetRepository, bool $isManager): Response
    {
        $resultSquads = $squads->findBy([], ['name' => 'ASC']);

        $mindsets = [];
        if ($isManager) {
            foreach ($resultSquads as $squad) {
                /**
                 * @var $mindset MindsetDTO
                 */
                $mindsetSquad = $mindsetRepository->getMindsetSquad($squad->getId());
                $mindsets += [$squad->getId() => $mindsetSquad];
            }
        }

        return new Response(
            $this->renderView('squad/index.html.twig', [
                'squads' => $resultSquads,
                'mindsetsSquad' => $mindsets,
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    #[Route("/{squadId}/add",name: "squad_add_user")]
    public function addUser(UserRepository $users, SquadRepository $squads, int $squadId = null): Response
    {
        $squad = $squads->find($squadId);

        return new Response(
            $this->renderView('squad/addUser.html.twig', [
                'squad' => $squad,
                'users' => $users->findBy([], ["fullName"=>'ASC'])
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    #[Route("/mindset/history/{squadId}",name: "squad_mindset_history", methods: ["GET"])]
    public function getMindsetHistory(int $squadId, MindsetRepository $mindsetRepository): Response
    {
        $result = $mindsetRepository->getMindsetHistorySquad($squadId);

        return $this->render('mindset/mindsetHistory.json.twig', [
            'mindsetHistory' => $result,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route("/delete/{squadId}",name: "squad_delete", methods: ["GET","POST"])]
    public function delete(SquadRepository $squadRepository, int $squadId, ManagerRegistry $managerRegistry): Response
    {
        /**
         * @var Squad $squad
         */
        $squad = $squadRepository->find($squadId);

        $this->addFlash('success', 'Squad ' . $squad->getName() . ' supprimée');

        $users = $squad->getUsers();

        $emSquad = $managerRegistry->getManagerForClass(Squad::class);
        $emUser = $managerRegistry->getManagerForClass(User::class);
        /**
         * @var User $user
         */
        foreach ($users as $user){
            $user->setSquad(null);
            $emUser->persist($user);
        }

        $emSquad->remove($squad);
        $emUser->flush();
        $emSquad->flush();

        return $this->redirectToRoute('squad_index');
    }
}
