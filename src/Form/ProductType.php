<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Category;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormEvent;

class ProductType extends ApplicationType
{
    /** @var SluggerInterface */
    private SluggerInterface $slugger;

    /**
     * @param SluggerInterface $slugger
     */
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mark', TextType::class, $this->getConfiguration("Marque", "La marque du produit ..."))
            ->add('price', IntegerType::class, $this->getConfiguration("Prix", "Le prix du produit ..."))
            ->add('description', TextareaType::class, $this->getConfiguration("Description", "La description du produit ..."))
            ->add('size', TextType::class, $this->getConfiguration("Taille", "La taille du produit ..."))
            ->add('sex', ChoiceType::class, [
                'choices' => [
                    'Masculin' => 'Masculin',
                    'féminin' => 'féminin'
                ],
                'expanded' => true,
                'multiple' => true,
                'label' => 'Genre'
            ])
            ->add('color', TextType::class, $this->getConfiguration("Couleur", "La couleur du produit ..."))
            ->add('images', FileType::class, $this->getConfiguration("Photos", "Les photos ...", ['mapped' => false, 'multiple' => true, 'required' => false]))
            ->add('category', EntityType::class, $this->getConfiguration("Catégorie", false, ['class' => Category::class, 'choice_label' => 'title']))
            
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var Product */
                $product = $event->getData();
                if (null !== $productMark = $product->getMark()) {
                    $product->setSlug($this->slugger->slug($productMark)->lower());
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
