<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DeskRepository;
use DateTime;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/badge")
 */
#[IsGranted('ROLE_SCREEN')]
class BadgeController extends AbstractController {

    #[Route("/admin",name: "badge_admin")]
    public function adminBadge(DeskRepository $deskRepository, Request $request): Response {

        $selectedDate = $request->query->get('selectedDate');

        $selectedDate = new DateTime($selectedDate);

        $deskList = $deskRepository->findAll();

        return $this->render('badge/admin.html.twig', [
            'badges' => $deskList,
            'selectedDate' => $selectedDate->format("Y-m-d"),
        ]);
    }

    #[Route("/list",name: "badge_list")]
    public function badgeList(DeskRepository $deskRepository, Request $request): Response {

        $selectedDate = $request->query->get('selectedDate');

        $selectedDate = new DateTime($selectedDate);

        $deskList = $deskRepository->findAll();

        return $this->render('badge/list.html.twig', [
            'badges' => $deskList,
            'selectedDate' => $selectedDate->format("Y-m-d"),
        ]);
    }

    #[Route("/sanction",name: "badge_sanction")]
    public function sanctionList(DeskRepository $deskRepository, Request $request): Response {

        $selectedDate = $request->query->get('selectedDate');

        $selectedDate = new DateTime($selectedDate);

        $deskList = $deskRepository->findAll();

        return $this->render('badge/sanction.html.twig', [
            'badges' => $deskList,
            'selectedDate' => $selectedDate->format("Y-m-d"),
        ]);
    }
}
