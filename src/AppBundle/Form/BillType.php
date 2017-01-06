<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class BillType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('date', 'date')
            ->add('deadlineDays')
            ->add('project', EntityType::class, [
                'class' => 'AppBundle:Project',
                'query_builder' => function ($er) use ($options) {
                    return $er
                        ->createQueryBuilder('p')
                        ->innerJoin('p.customer', 'c')
                        ->where('c.admin = :admin')
                        ->setParameter('admin', $options['admin'])
                        ->orderBy('p.name');
                }
            ])
            ->add('items', CollectionType::class, [
                'entry_type' => BillItemType::class,
                'allow_add' => true,
                'by_reference' => false
            ])
        ;

        if ($options['edit']) {
            $builder->add('accountBalance');
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Bill',
            'admin' => '',
            'edit' => false
        ));
    }
}
