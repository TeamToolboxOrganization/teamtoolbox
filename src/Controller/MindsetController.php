<?php

namespace App\Controller;

use App\Entity\Mindset;
use App\Entity\User;
use App\Form\MindsetType;
use App\Repository\UserRepository;
use App\Security\CSPDefinition;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller used to manage note contents in the backend.
 *
 * Please note that the application backend is developed manually for learning
 * purposes. However, in your real Symfony application you should use any of the
 * existing bundles that let you generate ready-to-use backends without effort.
 *
 * See http://knpbundles.com/keyword/admin
 */
#[Route("/mood")]
class MindsetController extends AbstractController
{
    /**
     * Creates a new Note entity.
     */
    #[Route("/new",name: "mindset_new", methods: ["GET", "POST"])]
    #[Route("/{userId}/new",name: "mindset_collab_new", methods: ["GET", "POST"])]
    public function new(Request $request, UserRepository $users, ManagerRegistry $managerRegistry, int $userId = null): Response
    {
        $mindset = new Mindset();
        $mindset->setAuthor($this->getUser());
        $mindset->setDate(new \DateTime());

        if ($userId != null) {
            /**
             * @var $currentUser User
             */
            $collabUser = $users->findOneBy([
                'id' => $userId,
            ]);

            $mindset->setCollab($collabUser);
        } else {
            $mindset->setCollab($this->getUser());
        }

        $form = $this->createForm(MindsetType::class, $mindset);

        $form->handleRequest($request);

        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See https://symfony.com/doc/current/forms.html#processing-forms
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $managerRegistry->getManagerForClass(Mindset::class);
            $em->persist($mindset);
            $em->flush();

            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.
            // See https://symfony.com/doc/current/controller.html#flash-messages
            $this->addFlash('success', "Mood ajouté");

            return $this->redirectToRoute('collab_index', ['userId' => $mindset->getCollab()->getId()]);
        }

        return new Response(
            $this->renderView('mindset/new.html.twig', [
                'mindset' => $mindset,
                'form' => $form->createView(),
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

}
