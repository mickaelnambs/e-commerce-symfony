<?php

namespace App\Form;

use App\Entity\ProductSearch;
use App\Form\ApplicationType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductSearchType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('equalMark', TextType::class, $this->getConfiguration(false, "Marque ...", ['required' => false]))
            ->add('equalPrice', IntegerType::class, $this->getConfiguration(false, "Prix ...", ['required' => false]))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductSearch::class,
            'csrf_protection' => false,
            'method' => 'GET'
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
