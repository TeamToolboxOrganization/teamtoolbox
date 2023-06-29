<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Security;

class XlsxExport extends AbstractType
{
    /**
     * @var Security
     */
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('workActivity', ChoiceType::class, [
                'required' => true,
                'label' => 'Activité par défaut',
                'choices' => [
                    'Utilisation fréquente' => [
                        "03. Development" => "03. Development",
                        "06. Requirements" => "06. Requirements",
                        "08. Testing : Manual Test" => "08. Testing : Manual Test",
                        "18. OoB : Time off" => "18. OoB : Time off"
                    ],
                    'Liste complète' => [
                        "00. Project management and meetings" => "00. Project management and meetings",
                        "01. Operations" => "01. Operations",
                        "02. Infrastructure" => "02. Infrastructure",
                        "03. Development" => "03. Development",
                        "04. Settings" => "04. Settings",
                        "05. Documentation" => "05. Documentation",
                        "06. Requirements" => "06. Requirements",
                        "07. Testing : Automated Tests" => "07. Testing : Automated Tests",
                        "08. Testing : Manual Test" => "08. Testing : Manual Test",
                        "09. Testing : Performance Test" => "09. Testing : Performance Test",
                        "10. OoB : [OBSELETE] Project management" => "10. OoB : [OBSELETE] Project management",
                        "11. OoB : Non-Project" => "11. OoB : Non-Project",
                        "12. OoB : Support Level 3" => "12. OoB : Support Level 3",
                        "13. OoB : Support Level 1 and 2" => "13. OoB : Support Level 1 and 2",
                        "14. OoB : Customer Services" => "14. OoB : Customer Services",
                        "15. OoB : Training Undertaken" => "15. OoB : Training Undertaken",
                        "16. OoB : Training Provided" => "16. OoB : Training Provided",
                        "17. OoB : Training School" => "17. OoB : Training School",
                        "18. OoB : Time off" => "18. OoB : Time off"
                    ]
                ]
            ])
            ->add('workItem', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'Utilisation fréquente' => [
                        "LMS" => "781361",
                        "LCMS" => "923866",
                        ],
                    'Liste complète' => [
                    "LMS" => "781361",
                    "LMS MOBILE APP" => "781362",
                    "LCMS" => "923866",
                    "LCMS DESKTOP APP" => "781360",
                    "TMS" => "781365"]
                ]
            ])
            ->add('startDate', DateType::class, [
                'required' => true,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ])
            ->add('endDate', DateType::class, [
                'required' => true,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ])
        ;
    }

}