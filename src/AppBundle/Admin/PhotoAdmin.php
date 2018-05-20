<?php
namespace AppBundle\Admin;

use Doctrine\ORM\Mapping as ORM;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

class PhotoAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
          ->add('nom', TextType::class)
          ->add('imageName', TextType::class)
          ->add('contenu', TextType::class)
          ->add('prix', IntegerType::class)
          ->add('dateCreation', DateType::class )
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
          ->add('nom')
          ->add('imageName')
          ->add('contenu')
          ->add('prix')
          ->add('dateCreation')

        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
          ->addIdentifier('nom')
          ->addIdentifier('imageName')
          ->addIdentifier('contenu')
          ->addIdentifier('prix')
          ->addIdentifier('dateCreation')

        ;
    }
}