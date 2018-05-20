<?php
// FormType de sauvegarde du formulaire pour le choix du rôle à l'inscription (/register)
// Dans le fichier: vendor/friendsofsymfony/user-bundle/Form/Type/RegistrationFormType.php
namespace AA\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('photographe', ChoiceType::class, array(
          'choices'  => array(
              'Photographe' => true,
              'Client' => false,
          ),
          'multiple' => false,
          'expanded' => true,
          'label' => "Vous êtes un...",
      ));
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'AA\UserBundle\Entity\User'
    ));
  }
}
