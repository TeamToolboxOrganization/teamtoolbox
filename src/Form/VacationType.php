<?php

namespace App\Form;

use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class VacationType extends AbstractType
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

    /**
     * @param TokenStorageInterface $token
     * @param UserRepository $userRepository
     * @param Security $security
     */
    public function __construct(TokenStorageInterface $token, UserRepository $userRepository, Security $security)
    {
        $this->token = $token;
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [
            "vacation.CPF" => "Congés payés",
            "vacation.RTT" => "RTT",
            "vacation.SansSolde" => "Congés sans solde",
        ];

        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'vacation.type',
                'choices' => $choices,
            ])
            ->add('startAtPm', CheckboxType::class,
                [
                    'label' => 'vacation.startPm',
                    'required' => false,
                ])
            ->add('endAtAm', CheckboxType::class,
                [
                    'label' => 'vacation.endAm',
                    'required' => false,
                ])
            ->add('startAt', DateType::class, [
                'label' => 'vacation.startDate',
                'widget' => 'single_text',
            ])
            ->add('endAt', DateType::class, [
                'label' => 'vacation.endDate',
                'widget' => 'single_text',
            ]);
    }
}
