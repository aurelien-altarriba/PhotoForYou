<?php

namespace AA\BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AA\PhotoforyouBundle\Entity\Photo;
use AA\PhotoforyouBundle\Form\PhotoType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    public function indexAction()
    {

        $listPhotos = $this->getDoctrine()
          ->getManager()
          ->getRepository('AAPhotoforyouBundle:Photo')
          ->getPhotosVedette()
        ;

        // On donne toutes les informations nécessaires à la vue
        return $this->render('@AABase\Default\index.html.twig', array(
          'listPhotos' => $listPhotos
        ));
    }

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * *
	
	// Manipulation des objets User gérés par FOSUser

	// Pour récupérer le service UserManager du bundle
	$userManager = $this->get('fos_user.user_manager');

	// Pour charger un utilisateur
	$user = $userManager->findUserBy(array('username' => 'winzou'));

	// Pour modifier un utilisateur
	$user->setEmail('cetemail@nexiste.pas');
	$userManager->updateUser($user); // Pas besoin de faire un flush avec l'EntityManager, cette méthode le fait toute seule !

	// Pour supprimer un utilisateur
	$userManager->deleteUser($user);

	// Pour récupérer la liste de tous les utilisateurs
	$users = $userManager->findUsers();

	* * * * * * * * * * * * * * * * * * * * * * * * * * * */
}
