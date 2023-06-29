<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Desk;
use App\Entity\Note;
use App\Entity\Squad;
use App\Entity\User;
use App\Form\Type\DateTimePickerType;
use App\Repository\CategoryRepository;
use App\Repository\DeskRepository;
use App\Repository\SquadRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Defines the form used to edit an user.
 */
class UserType extends AbstractType
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var SquadRepository
     */
    private SquadRepository $squadRepository;

    /**
     * @var DeskRepository
     */
    private DeskRepository $deskRepository;

    /**
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepository;

    /**
     * @var Security
     */
    private Security $security;

    public function __construct(TokenStorageInterface $token, UserRepository $userRepository, SquadRepository $squadRepository, DeskRepository $deskRepository, CategoryRepository $categoryRepository, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->squadRepository = $squadRepository;
        $this->deskRepository = $deskRepository;
        $this->categoryRepository = $categoryRepository;
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isAdmin = $this->security->isGranted("ROLE_ADMIN");
        $isEdition = ($options['mode'] === 'edition');

        // For the full reference of options defined by each form field type
        // see https://symfony.com/doc/current/reference/forms/types.html

        // By default, form fields include the 'required' attribute, which enables
        // the client-side form validation. This means that you can't test the
        // server-side validation errors from the browser. To temporarily disable
        // this validation, set the 'required' attribute to 'false':
        // $builder->add('title', null, ['required' => false, ...]);

        $builder
            ->add('username', TextType::class, [
                'label' => 'label.username',
                'disabled' => $isEdition,
            ]);

        if(!$isEdition){
            $builder
                ->add('password', PasswordType::class, [
                    'label' => 'Password',
                    'required' => false,
                ]);
        }

        $builder
            ->add('fullName', TextType::class, [
                'label' => 'label.fullname',
            ])
            ->add('email', EmailType::class, [
                'label' => 'label.email',
            ])
            ->add('picture', FileType::class, [
                // unmapped means that this field is not associated to any entity property
                'mapped' => false,
                'label' => 'Image de profil',

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,
            ])
            ->add('birthday', BirthdayType::class, [
                'label' => 'Date de naissance',
                'html5' => true,
                'required' => false,
            ])
            ->add('defaultActivity', EntityType::class, [
                'label' => 'Activité par défaut',
                'class' => Category::class,
                'choices' => $this->categoryRepository->findBy([], ['name' => 'ASC']),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'placeholder' => 'Activité perso',
                'required' => false,
            ])
            ->add('defaultProduct', ChoiceType::class, [
                'required' => false,
                'label' => 'Produit par défaut',
                'choices' => [
                    'Choisir votre produit par défaut :' => [
                        'Produit 1' => "1",
                        'Produit 2' => "2",
                    ],
                ],
            ])
            ->add('defaultDesk', EntityType::class, [
                'label' => 'Bureau par défaut',
                'class' => Desk::class,
                'choices' => $this->deskRepository->findBy([], ['name' => 'ASC']),
                'choice_label' => 'name',
                'placeholder' => 'Bureau perso',
                'required' => false,
            ]);

        if($isAdmin) {
            $builder
                ->add('manager', EntityType::class, [
                    'class' => User::class,
                    'choices' => $this->userRepository->findBy([], ['fullName' => 'ASC']),
                    'choice_label' => 'fullName',
                    'placeholder' => 'Choisir le manager',
                    'required' => false,
                ])
                ->add('squad', EntityType::class, [
                    'label' => 'Équipe',
                    'class' => Squad::class,
                    'choices' => $this->squadRepository->findBy([], ['name' => 'ASC']),
                    'choice_label' => 'name',
                    'placeholder' => 'Choisir la squad',
                    'required' => false,
                ])
                ->add('roles', ChoiceType::class, [
                    'choices'  => [
                        User::ROLE_ADMIN => 'ROLE_ADMIN',
                        User::ROLE_MANAGER => 'ROLE_MANAGER',
                        User::ROLE_LT => 'ROLE_LT',
                        User::ROLE_MEP_ORGA => 'ROLE_MEP_ORGA',
                        User::ROLE_USER => 'ROLE_USER',
                        User::ROLE_SCREEN => 'ROLE_SCREEN',
                    ],
                    'multiple' => true,
                ])
            ;
        }

        $builder
            ->add('sharedata', CheckboxType::class, [
                'label' => 'Partager photo et anniversaire avec ses collègues',
                'required' => false,
            ])
            ->add('analytics', CheckboxType::class, [
                'label' => 'Activer Google Analytics',
                'required' => false,
            ])
            ->add('wizard', CheckboxType::class, [
                'label' => 'Wizard',
                'required' => false,
            ])
            ->add('apikeyjira', TextType::class, [
                'label' => 'API Key Jira',
                'required' => false,
            ])
            ->add('apikeyazdo', TextType::class, [
                'label' => 'API Key Azure DevOPS',
                'required' => false,
            ])
        ;

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'mode' => "edition"
        ]);
    }
}
