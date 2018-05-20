<?php
namespace AA\PhotoforyouBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AA\PhotoforyouBundle\Entity\Categorie;

class LoadCategorie implements FixtureInterface
{
  // Dans l'argument de la méthode load, l'objet $manager est l'EntityManager
  public function load(ObjectManager $manager)
  {
    // Liste des noms de catégorie à ajouter
    $names = array(
      'Paysage',
      'Montagne',
      'Mer',
      'Nature',
      'Animaux',
      'Portrait',
      'Enfant',
      'Divers',
      'Objet',
      'Événement',
      'Rivière',
      'Vêtements',
      'Famille',
      'Nature morte',
      'Technologie',
      'Art',
      'Transport',
      'Île',
      'Architecture'
    );

    foreach ($names as $name) {
      // On crée la catégorie
      $categorie = new Categorie();
      $categorie->setName($name);

      // On la persiste
      $manager->persist($categorie);
    }

    // On déclenche l'enregistrement de toutes les catégories
    $manager->flush();
  }
}
