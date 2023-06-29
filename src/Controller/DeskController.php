<?php

namespace App\Controller;

use App\Entity\Desk;
use App\Entity\DeskDate;
use App\Entity\DeskDTO;
use App\Entity\Office;
use App\Entity\User;
use App\Repository\DeskDateRepository;
use App\Repository\DeskRepository;
use App\Repository\OfficeRepository;
use App\Repository\UserRepository;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[IsGranted('ROLE_SCREEN')]
#[Route("/desk")]
class DeskController extends AbstractController {
    /**
     * @param Desk $desk
     * @param RouterInterface $router
     *
     * @return Response
     */
    #[Route("/qrcode/{id}",name: "desk_qrcode", methods: ["GET"])]
    #[ParamConverter('id', class: 'App\Entity\Desk')]
    public function qrCode(Desk $desk, RouterInterface $router): Response {
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);

        $url = $router->generate('desk_scan', ['id' => $desk->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->render('desk/qr-code.html.twig', [
            'qrcode' => 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($url)),
            'title' => $desk->getName() ?? $desk->getId(),
            'url' => $url,
        ]);
    }

    /**
     * @param Desk $desk
     * @param DeskDateRepository $deskDateRepository
     * @param ManagerRegistry $managerRegistry
     *
     * @return Response
     * @throws \Exception
     */
    #[Route("/scan/{id}",name: "desk_scan", methods: ["GET"])]
    #[ParamConverter('id', class: 'App\Entity\Desk')]
    public function scanDesk(Request $request, Desk $desk, DeskDateRepository $deskDateRepository, OfficeRepository $officeRepository, UserRepository $userRepository, ManagerRegistry $managerRegistry, Security $security): Response {
        $date = $request->query->get('selectedDate');

        [$result, $success] = $this->doScanDesk($date, $deskDateRepository, $officeRepository, $userRepository, $desk, $managerRegistry, $security);

        return $this->render('desk/scanResult.html.twig', [
            'result' => $result,
            'success' => $success,
        ]);
    }

    #[Route("/view",name: "desk_view", methods: ["GET"])]
    public function drawPlan(Request $request): Response {

        $selectedDate = $request->query->get('selectedDate');

        $selectedDate = new DateTime($selectedDate);

        return $this->render('desk/view.html.twig', [
            'selectedDate' => $selectedDate->format("Y-m-d"),
        ]);
        //
    }

    #[Route("/list",name: "desk_list", methods: ["GET"])]
    public function deskList(DeskRepository $deskRepository): Response {

        $deskList = $deskRepository->findAll();

        return $this->render('desk/list.html.twig', [
            'desks' => $deskList,
        ]);
        //
    }

    /**
     * @param DeskRepository $repository
     * @param SerializerInterface $serializer
     * @param User $user
     *
     * @return Response
     */
    #[Route("/api/list",name: "api_desk_list", methods: ["GET"])]
    public function apiDesks(Request $request, DeskRepository $repository, OfficeRepository $officeRepository, SerializerInterface $serializer, UserInterface $user): Response {

        $date = $request->query->get('selectedDate');
        if(!empty($date)){
            $date = new DateTime($date);
            $date = new DateTime($date->format("Y-m-d") . " 00:00:00");
        } else {
            // Create Today Date time
            $today = new DateTime();
            $date = new DateTime($today->format("Y-m-d") . " 00:00:00");
        }

        $officeDates = $officeRepository->findBy(['startAt' => $date]);

        $officeDeskUsed = [];
        foreach ($officeDates as $officeDate){
            $defaultDesk = $officeDate->getCollab()->getDefaultDesk();
            if($defaultDesk != null){
                $officeDeskUsed[$defaultDesk->getId()] = $officeDate->getCollab();
            }
        }

        $desks = $repository->findAll();
        $currentDate = $date->format('Y-m-d');

        $data = array_map(function (Desk $desk) use ($user, $currentDate, $officeDeskUsed) {
            $deskDates = $desk->getDeskDates()->filter(function (DeskDate $deskDate) use ($currentDate) {
                return $deskDate->getStartAt()->format('Y-m-d') == $currentDate;
            });

            $elem = new DeskDTO();
            $elem->setId($desk->getId());
            $elem->setX($desk->getX() ?? 0);
            $elem->setY($desk->getY() ?? 0);
            $elem->setName($desk->getName() ?? '');

            if (!$deskDates->isEmpty()) {
                $elem->setAvailable(false);
                /** @var DeskDate $deskDate */
                $deskDate = $deskDates->first();
                $elem->setMe($deskDate->getCollab()->getId() === $user->getId());
            }
            elseif(key_exists($desk->getId(), $officeDeskUsed)){
                $elem->setAvailable(false);
                $elem->setMe($officeDeskUsed[$desk->getId()]->getId() === $user->getId());
            }

            return $elem;
        }, $desks);



        return new Response($serializer->serialize($data, 'json'), 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @param Desk $desk
     * @param DeskDateRepository $deskDateRepository
     * @param ManagerRegistry $managerRegistry
     *
     * @return Response
     * @throws \Exception
     */
    #[Route("/api/scan/{id}",name: "api_desk_scan", methods: ["GET"])]
    #[ParamConverter('id', class: 'App\Entity\Desk')]
    public function apiScanDesk(Request $request, Desk $desk, DeskDateRepository $deskDateRepository, OfficeRepository $officeRepository, UserRepository $userRepository, ManagerRegistry $managerRegistry, Security $security): Response {
        $date = $request->query->get('selectedDate');

        [$result, $success] = $this->doScanDesk($date, $deskDateRepository, $officeRepository, $userRepository, $desk, $managerRegistry, $security);

        return $this->json([
            'result' => $result,
            'success' => $success,
        ]);
    }

    /**
     * @param DeskDateRepository $deskDateRepository
     * @param Desk $desk
     * @param ManagerRegistry $managerRegistry
     *
     * @return array
     * @throws \Exception
     */
    protected function doScanDesk(?string $date, DeskDateRepository $deskDateRepository, OfficeRepository $officeRepository, UserRepository $userRepository, Desk $desk, ManagerRegistry $managerRegistry, Security $security): array {
        /**
         * @var $user User
         */
        $user = $this->getUser();

        if(!empty($date)){
            $date = new DateTime($date);
            $date = new DateTime($date->format("Y-m-d") . " 00:00:00");
        } else {
            // Create Today Date time
            $today = new DateTime();
            $date = new DateTime($today->format("Y-m-d") . " 00:00:00");
        }

        // Check if desk owner go to office today
        $deskUsers = $userRepository->findBy(['defaultDesk' => $desk]);
        if($deskUsers != null){
            $officeDate = $officeRepository->findBy(['startAt' => $date, 'collab' => $deskUsers[0]]);
            if(!empty($officeDate)){
                $result = 'Déjà réservé par ' .  $deskUsers[0]->getFullName();
                return [$result, false];
            }
        }

        // Check if current desk is used today
        $existingResa = $deskDateRepository->findBy(['desk' => $desk->getId(), 'startAt' => $date]);

        $result = 'Bureau réservé';
        $success = true;

        // If no reservation
        if (empty($existingResa)) {
            if ($security->isGranted('ROLE_USER')) {
                // Create reservation
                $newResa = new DeskDate();
                $newResa->setDesk($desk);
                $newResa->setCollab($user);
                $newResa->setStartAt($date);
                $managerRegistry->getManager()->persist($newResa);
                $managerRegistry->getManager()->flush();
            }
        } else {
            if ($existingResa[0]->getCollab() == $user) {
                if ($security->isGranted('ROLE_USER')) {
                    $managerRegistry->getManager()->remove($existingResa[0]);
                    $managerRegistry->getManager()->flush();
                    $result = 'Réservation supprimée';
                }
            } else {
                $result = 'Déjà réservé par ' . $existingResa[0]->getCollab()->getFullName();
                $success = false;
            }
        }

        return [$result, $success];
    }

}
