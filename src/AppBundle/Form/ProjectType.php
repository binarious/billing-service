<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ProjectType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('customer', EntityType::class, [
                'class' => 'AppBundle:Customer',
                'query_builder' => function ($er) use ($options) {
                    return $er
                        ->createQueryBuilder('c')
                        ->where('c.admin = :admin')
                        ->orderBy('c.name')
                        ->setParameter('admin', $options['admin']);
                }
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Project',
            'admin' => ''
        ));
    }
}
