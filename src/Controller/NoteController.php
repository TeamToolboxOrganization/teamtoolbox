<?php

namespace App\Controller;

use App\Entity\Mindset;
use App\Entity\Note;
use App\Entity\User;
use App\Entity\UserDate;
use App\Form\NoteType;
use App\Repository\MepRepository;
use App\Repository\NoteRepository;
use App\Repository\UserRepository;
use App\Security\CSPDefinition;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Controller used to manage note contents in the backend.
 *
 * Please note that the application backend is developed manually for learning
 * purposes. However, in your real Symfony application you should use any of the
 * existing bundles that let you generate ready-to-use backends without effort.
 *
 * See http://knpbundles.com/keyword/admin
 */
#[IsGranted('ROLE_USER')]
#[Route("/note")]
class NoteController extends AbstractController
{
    #[Route("/",name: "note_index")]
    public function index(Request $request, NoteRepository $notes, Security $security): Response
    {
        /**
         * @var $currentUser User
         */
        $currentUser = $this->getUser();

        $allNotes = $notes->findBy([
            'author' => $currentUser,
        ], ['publishedAt' => 'DESC',], 20);

        return new Response(
            $this->renderView('note/mynotes.html.twig', [
                'title' => "Notes",
                'allNotes' => $allNotes,
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy-Report-Only' => CSPDefinition::defaultRules
            ]
        );
    }

    /**
     * Creates a new Note entity.
     */
    #[Route("/new",name: "manager_note_new", methods: ["GET", "POST"])]
    #[Route("/{userId}/new",name: "manager_note_collab_new", methods: ["GET", "POST"])]
    public function new(Request $request, UserRepository $users, ManagerRegistry $managerRegistry, int $userId = null): Response
    {
        $currentDate = new \DateTime();
        $note = new Note();
        $note->setAuthor($this->getUser());
        $note->setType(Note::TYPE_ONETOONE);
        $note->setPublishedAt($currentDate);

        if($userId != null){
            /**
             * @var $currentUser User
             */
            $collabUser = $users->findOneBy([
                'id' => $userId,
            ]);

            $note->setCollab($collabUser);
        }

        $form = $this->createForm(NoteType::class, $note);

        $form->handleRequest($request);


        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See https://symfony.com/doc/current/forms.html#processing-forms
        if ($form->isSubmitted() && $form->isValid()) {
            $mindset = null;
            if($note->getMindsetValue() !== null){
                $mindset = new Mindset();
                $mindset->setAuthor($this->getUser());
                $mindset->setDate($currentDate);
                $mindset->setValue($note->getMindsetValue());
                $mindset->setCollab($note->getCollab());
                $note->setMindset($mindset);
            }

            $emMindset = $managerRegistry->getManagerForClass(Mindset::class);
            if($mindset){
                $emMindset->persist($mindset);
            }

            $em = $managerRegistry->getManagerForClass(Note::class);
            $em->persist($note);
            $em->flush();
            $emMindset->flush();

            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.
            // See https://symfony.com/doc/current/controller.html#flash-messages
            $this->addFlash('success', 'Enregistrement en cours ...');

            //return $this->redirectToRoute('dashboard_index');
        }

        return new Response(
            $this->renderView('note/new.html.twig', [
                'note' => $note,
                'form' => $form->createView(),
                'userId' => $userId,
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy-Report-Only' => CSPDefinition::defaultRules
            ]
        );
    }

    #[Route("/delete/{noteId}",name: "note_delete", methods: ["GET", "POST"])]
    public function delete(NoteRepository $noteRepository, ManagerRegistry $managerRegistry, int $noteId): Response
    {
        /**
         * @var $currentUser User
         */
        $currentUser = $this->getUser();

        $note = $noteRepository->findOneBy([
            'id' => $noteId,
            'author' => $currentUser,
        ]);

        $managerRegistry->getManager()->remove($note);
        $managerRegistry->getManager()->flush();

        return new Response($noteId, Response::HTTP_OK);
    }

}
