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
                        "00. Gestion de projet" => "00. Gestion de projet",
                        "01. Dev" => "01. Dev",
                    ],
                    'Liste complète' => [
                        "00. Gestion de projet" => "00. Gestion de projet",
                        "01. Dev" => "01. Dev",
                        "02. Off" => "02. Off"
                    ]
                ]
            ])
            ->add('workItem', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'Utilisation fréquente' => [
                        "Produit 1" => "Produit 1",
                        "Produit 2" => "Produit 2",
                        ],
                    'Liste complète' => [
                    "Produit 1" => "Produit 1",
                    "Produit 2" => "Produit 2",
                    "Produit 3" => "Produit 3",
                    "Produit 4" => "Produit 4",
                    ]
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