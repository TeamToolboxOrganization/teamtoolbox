<?php

namespace App\Form;

use App\Entity\Mep;
use App\Entity\User;
use App\Repository\UserRepository;

use App\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * See https://symfony.com/doc/current/forms.html#creating-form-classes
 */
class MepType extends AbstractType
{

    /**
     * @var TokenStorageInterface
     */
    private $token;

    /**
     * @var $er UserRepository
     */
    private $userRepository;

    public function __construct(TokenStorageInterface $token, UserRepository $userRepository)
    {
        $this->token = $token;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * @var $currentUser User
         */
        $currentUser = $this->token->getToken()->getUser();

        // By default, form fields include the 'required' attribute, which enables
        // the client-side form validation. This means that you can't test the
        // server-side validation errors from the browser. To temporarily disable
        // this validation, set the 'required' attribute to 'false':
        // $builder->add('content', null, ['required' => false]);

        $builder
            ->add('startAt', DateTimePickerType::class, [
                'label' => 'label.published_at',
                'html5' => true,
            ])
            ->add('collab', EntityType::class, [
                'class' => User::class,
                'choices' => $this->userRepository->findBy([], ['fullName' => 'ASC']),
                'choice_label' => 'fullName',
                'placeholder' => 'Choose a collab',
                'required' => false,
            ])
            ->add('version', TextType::class, [
                'label' => 'Version',
                'required' => true,
            ])
            ->add('state', ChoiceType::class, [
                'choices' => [Mep::STATE_TOCONFIRM => Mep::STATE_TOCONFIRM , Mep::STATE_CONFIRM => Mep::STATE_CONFIRM, Mep::STATE_CANCELED => Mep::STATE_CANCELED],
                'label' => 'Etat',
                'required' => true,
            ])
            ->add('comment', TextType::class, [
                'label' => 'Commentaire',
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
            'data_class' => Mep::class,
        ]);
    }
}
