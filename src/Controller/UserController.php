<?php

namespace App\Controller;

use App\Entity\DeskDate;
use App\Entity\Mep;
use App\Entity\ObjectiveTheme;
use App\Entity\Office;
use App\Entity\User;
use App\Entity\UserDate;
use App\Entity\Vacation;
use App\Form\Type\ChangePasswordType;
use App\Form\UserType;
use App\Repository\MepRepository;
use App\Repository\ObjectiveThemeRepository;
use App\Repository\UserRepository;
use App\Security\CSPDefinition;
use Doctrine\Persistence\ManagerRegistry;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\SluggerInterface;


/**
 * Controller used to manage current user.
 **/
#[IsGranted('ROLE_USER')]
#[Route("/user")]
class UserController extends AbstractController
{

    private const MAX_WIDTH = 128;
    private const MAX_HEIGHT = 128;

    #[IsGranted('ROLE_ADMIN')]
    #[Route("/new",name: "user_new", methods: ["GET","POST"])]
    public function new(Request $request, SluggerInterface $slugger, ManagerRegistry $managerRegistry, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository): Response
    {
        $user = new User();
        $user->setBirthday(new \DateTime());

        $form = $this->createForm(UserType::class, $user, ['mode' => 'create']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->uploadPicture($form, $slugger, $user);

            if (!empty($user->getPassword())) {
                $user->setPassword($passwordHasher->hashPassword($user, $form->get('password')->getData()));
            }

            $em = $managerRegistry->getManagerForClass(User::class);
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur ' . $user->getFullName() . ' créé');

            return $this->redirectToRoute('user_list_admin');
        }

        return new Response(
            $this->renderView('user/edit.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );

    }

    private function uploadPicture(FormInterface $form, SluggerInterface $slugger, User $user)
    {
        /** @var UploadedFile $pictureFile */
        $pictureFile = $form->get('picture')->getData();

        // this condition is needed because the 'brochure' field is not required
        // so the PDF file must be processed only when a file is uploaded
        if ($pictureFile) {

            //$pictureFileLite = $this->resize_image($pictureFile, 128, 128, true);

            $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $pictureFile->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $pictureFile->move(
                    $this->getParameter('pictures_directory'),
                    $newFilename
                );

                $this->resize($this->getParameter('pictures_directory') . '/' . $newFilename);
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            // updates the 'brochureFilename' property to store the PDF file name
            // instead of its contents
            $user->setPicture($newFilename);
        }
    }

    public function resize(string $filename): void
    {
        list($iwidth, $iheight) = getimagesize($filename);
        $ratio = $iwidth / $iheight;
        $width = self::MAX_WIDTH;
        $height = self::MAX_HEIGHT;
        if ($width / $height > $ratio) {
            $width = $height * $ratio;
        } else {
            $height = $width / $ratio;
        }

        $imagine = new Imagine();
        $photo = $imagine->open($filename);
        $photo->resize(new Box($width, $height))
            ->crop(new Point(0, 0), new Box(self::MAX_WIDTH, self::MAX_HEIGHT))
            ->save($filename);
    }

    #[Route("/edit",name: "user_edit", methods: ["GET","POST"])]
    #[Route("/edit/{userId}",name: "user_edit_him", methods: ["GET","POST"])]
    public function edit(Request $request, SluggerInterface $slugger, Security $security, UserRepository $userRepository, ManagerRegistry $managerRegistry, int $userId = null): Response
    {
        if (!$security->isGranted('ROLE_ADMIN')) {
            $userId = null;
        }

        /**
         * @var $user User
         */
        $user = null;
        if ($userId !== null) {
            $user = $userRepository->find($userId);
        } else {
            $user = $this->getUser();
        }

        $form = $this->createForm(UserType::class, $user, ['mode' => 'edition']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->uploadPicture($form, $slugger, $user);

            $managerRegistry->getManagerForClass(User::class)->flush();

            if ($security->isGranted('ROLE_ADMIN')) {
                $this->addFlash('success', 'Utilisateur ' . $user->getFullName() . ' mis à jour ');
                return $this->redirectToRoute('user_list_admin');
            }

            $this->addFlash('success', 'Mise à jour effectuée');
            return $this->redirectToRoute('user_edit');
        }

        return new Response(
            $this->renderView('user/edit.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    #[Route("/change-password",name: "user_change_password", methods: ["GET","POST"])]
    #[Route("/change-password/{userId}",name: "user_change_password_him", methods: ["GET","POST"])]
    public function changePassword(Request $request, Security $security, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher,  ManagerRegistry $managerRegistry, int $userId = null): Response
    {
        if (!$security->isGranted('ROLE_ADMIN')) {
            $userId = null;
        }

        /**
         * @var $user User
         */
        $user = null;
        if ($userId !== null) {
            $user = $userRepository->find($userId);
        } else {
            $user = $this->getUser();
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('newPassword')->getData()));

            $managerRegistry->getManagerForClass(User::class)->flush();

            return $this->redirectToRoute('security_logout');
        }

        return new Response(
            $this->renderView('user/change_password.html.twig', [
                'form' => $form->createView(),
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route("/list",name: "user_list_admin", methods: ["GET"])]
    public function getUserList(Request $request, UserRepository $userRepository): Response
    {
        $users = $userRepository->findBy(
            [

            ],
            [
                'fullName' => 'ASC'
            ]
        );

        return new Response(
            $this->renderView('user/usersListAdmin.html.twig', [
                'users' => $users,
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route("/delete/{userId}",name: "user_delete", methods: ["GET"])]
    public function delete(UserRepository $userRepository, MepRepository $mepRepository, ObjectiveThemeRepository $objectiveThemeRepository, ManagerRegistry $managerRegistry, int $userId): Response
    {
        /**
         * @var User $user
         */
        $user = $userRepository->find($userId);

        $emMep = $managerRegistry->getManagerForClass(Mep::class);
        $emUser = $managerRegistry->getManagerForClass(Mep::class);
        $emObjtheme = $managerRegistry->getManagerForClass(Mep::class);

        $mepDates = $mepRepository->findBy(['collab' => $userId]);
        $userName = $user->getFullName();

        $emVacation = $managerRegistry->getManagerForClass(Vacation::class);
        $vacationRepository = $emVacation->getRepository(Vacation::class);
        $vacations = $vacationRepository->findBy(['collab' => $user]);

        foreach ($vacations as $vacation){
            $emVacation->remove($vacation);
        }
        $emVacation->flush();

        $emOfficeDate = $managerRegistry->getManagerForClass(Office::class);
        $officeDateRepository = $emOfficeDate->getRepository(Office::class);
        $officeDates = $officeDateRepository->findBy(['collab' => $user]);

        foreach ($officeDates as $officeDate){
            $emOfficeDate->remove($officeDate);
        }
        $emOfficeDate->flush();

        $emDeskDate = $managerRegistry->getManagerForClass(DeskDate::class);
        $deskDateRepository = $emDeskDate->getRepository(DeskDate::class);
        $deskDates = $deskDateRepository->findBy(['collab' => $user]);

        foreach ($deskDates as $deskDate){
            $emDeskDate->remove($deskDate);
        }
        $emDeskDate->flush();

        /**
         * @var Mep $mep
         */
        foreach ($mepDates as $mep){
            $mep->setCollab(null);
            $emMep->persist($mep);
        }

        $objectiveThemes = $user->getObjectiveThemes();

        /**
         * @var ObjectiveTheme $objectiveTheme
         */
        foreach ($objectiveThemes as $objectiveTheme){
            $objectiveTheme->removeUser($user);
            $emObjtheme->persist($objectiveTheme);

            $user->removeObjectiveTheme($objectiveTheme);
            $emUser->persist($user);
            $emMep->flush();
            $emUser->flush();
            $emObjtheme->flush();
        }

        $usersForManager = $userRepository->getUsersForManager($userId);
        /**
         * @var User $userForManager
         */
        foreach ($usersForManager as $userForManager){
            $userForManager->setManager(null);
        }

        $emUser->remove($user);
        $emUser->flush();

        $this->addFlash('success', 'Utilisateur ' . $userName . ' supprimé');
        return $this->redirectToRoute('user_list_admin');
    }
}
