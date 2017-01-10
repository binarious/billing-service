<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class BillItemType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', [
                'attr' => [
                    'placeholder' => 'Titel'
                ]
            ])
            ->add('description', 'text', [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Beschreibung (optional)'
                ]
            ])
            ->add('quantity', 'text', [
                'attr' => [
                    'placeholder' => 'Anz.'
                ]
            ])
            ->add('amount', 'text', [
                'attr' => [
                    'placeholder' => 'Preis'
                ]
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\BillItem'
        ));
    }
}
