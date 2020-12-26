<?php

namespace App\Form;

use App\Entity\Contact;
use App\Form\ApplicationType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, $this->getConfiguration("Email", "Adresse email ..."))
            ->add('message', TextareaType::class, $this->getConfiguration("Message", "Votre message ..."))
            ->add('phone', TextType::class, $this->getConfiguration("Téléphone", "Numero telephone ..."));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
