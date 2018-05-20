<?php

namespace AA\PhotoforyouBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Vich\UploaderBundle\Form\Type\VichImageType;

class PhotoType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('nom', TextType::class, array(
            'label' => 'Nom de la photographie'
          ))
          ->add('contenu', TextareaType::class, array(
            'label' => 'Description de la photographie'
          ))
          ->add('prix', IntegerType::class, array(
            'label' => 'Prix (en crédits)',
            'attr'  => array(
              'min' => 2,
              'max' => 100
            )
          ))
          ->add('imageFile', VichImageType::class, [
            'required'       => true,
            'allow_delete'   => true,
            'download_label' => true,
            'download_uri'   => true,
            'image_uri'      => true,
            'label'          => 'Photographie (format JPEG seulement)'
          ])
          ->add('categories', EntityType::class, array(
            'class'        => 'AAPhotoforyouBundle:Categorie',
            'choice_label' => 'name',
            'multiple'     => true,
            'label'        => 'Catégories de la photographie (restez appuyés sur Ctrl en cliquant pour en choisir plusieurs)'
          ))
          ->add('sauvegarder', SubmitType::class, array(
            'label' => 'Ajouter au catalogue'
          ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AA\PhotoforyouBundle\Entity\Photo'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aa_photoforyoubundle_photo';
    }


}
