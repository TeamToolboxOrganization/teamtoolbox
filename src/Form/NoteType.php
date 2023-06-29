<?php

namespace App\Form;

use App\Entity\Note;
use App\Entity\User;
use App\Repository\UserRepository;

use App\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * See https://symfony.com/doc/current/forms.html#creating-form-classes
 */
class NoteType extends AbstractType
{

    /**
     * @var TokenStorageInterface
     */
    private $token;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var Security
     */
    private Security $security;

    public function __construct(TokenStorageInterface $token, UserRepository $userRepository, Security $security)
    {
        $this->token = $token;
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isManager = $this->security->isGranted("ROLE_MANAGER");

        $choices = [
            Note::TYPE_NOTE => Note::TYPE_NOTE,
            Note::TYPE_TODO => Note::TYPE_TODO,
            Note::TYPE_TODISCUSS => Note::TYPE_TODISCUSS,
        ];

        if ($isManager) {
            $choices = [
                Note::TYPE_ONETOONE => Note::TYPE_ONETOONE,
                Note::TYPE_NOTE => Note::TYPE_NOTE,
                Note::TYPE_TODO => Note::TYPE_TODO,
                Note::TYPE_TODISCUSS => Note::TYPE_TODISCUSS,
                Note::TYPE_TOFOLLOW => Note::TYPE_TOFOLLOW,
            ];
        }

        $builder
            ->add('content', TextareaType::class, [
                'attr' => ['class' => 'tinymce'],
                'help' => 'help.comment_content',
            ])
            ->add('type', ChoiceType::class, [
                'choices' => $choices,
            ])
            ->add('publishedAt', DateTimePickerType::class, [
                'label' => 'label.published_at',
                'html5' => true,
            ])
            ->add('collab', EntityType::class, [
                'class' => User::class,
                'choices' => $this->userRepository->findBy([],['fullName' => 'ASC']),
                'choice_label' => 'fullName',
                'placeholder' => 'Choose a collab',
                'required' => false,
            ]);

        if ($isManager) {
            $builder
                ->add('mindsetValue', NumberType::class, [
                    'required' => false,
                    'scale' => 1,
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Note::class,
        ]);
    }
}
