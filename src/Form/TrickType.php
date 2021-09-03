<?php

namespace App\Form;

use App\Entity\Groupe;
use App\Entity\Trick;
use App\Form\Type\CkeditorType;
use App\Repository\TrickRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('groupe', EntityType::class, [
                'class'  => Groupe::class,
                'choice_label'  => 'name',
                'multiple'  => false,
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
            ));


        $formModifier = function (FormInterface $form, Groupe $groupe = null) {

            $form->add('name', EntityType::class, [
                'class' => Trick::class,
                'query_builder' => function (TrickRepository $er) use ($groupe) {
                    return $er->createQueryBuilder('t')
                              ->join('t.groupe','g')
                              ->where('g.name =:groupe')
                              ->setParameter(':groupe', $groupe->getName())
                              ->andWhere('t.author is NULL');
                },
                'choice_label'  => 'name',
                'label' => 'Nom de la figure',
                'choice_value' => function (Trick|String|Null $trick) {
                    if($trick instanceof Trick){
                        return $trick->getId();
                    }
                    return $trick;
                },
            ]);
        };

        //Pour la fenêtre d'édition de figure : on affiche les noms de figures correspondant au groupe de la figure
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                // On récupère l'entité Trick()
                $trick = $event->getData();
                $formModifier($event->getForm(), $trick->getGroupe());
            }
        );

        $builder->get('groupe')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // on obtient l'entité Groupe d'id = valeur sélectionnée dans le formulaire
                $groupe = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $groupe);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
