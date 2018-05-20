<?php

namespace AA\PhotoforyouBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PhotoModifierType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->remove('imageFile');
  }

  public function getParent()
  {
    return PhotoType::class;
  }
}
