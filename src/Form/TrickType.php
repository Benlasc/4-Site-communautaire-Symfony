<?php

namespace App\Form;

use App\Entity\Trick;
use App\Form\Type\CkeditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',TextType::class,array(
                'label' => "Nom"
            ))
            ->add('groupe', ChoiceType::class, [
                'choices'  => [
                    'Grabs' => 'Grabs',
                    'Rotations' => 'Rotations',
                    'Rotations désaxées' => 'Rotations désaxées',
                    'Flips' => 'Flips',
                    'Slides' => 'Slides',
                    'One foot tricks' => 'One foot tricks',
                    'Old school' => 'Old school'
                ],
            ])
            ->add('description', CkeditorType::class, [
                'required' => false
            ])
            ->add('videos', CollectionType::class, array(
                'entry_type' => VideoType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'required' => true
            ))
            ->add('images', CollectionType::class, array(
                'entry_type' => ImageType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'required' => true
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
