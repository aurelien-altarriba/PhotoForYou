<?php

namespace AA\PanierBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use AA\UserBundle\Entity\User;

class PanierController extends Controller
{
    /**
     * @Route("/panier")
     *
     * @Security("has_role('ROLE_CLIENT')")
     */
    public function panierAction(Request $request)
    {
        // Si on peut récupérer la session
        if ($session = $request->getSession()) {
            $em = $this->getDoctrine()->getManager();
            $totalCout = 0;

            // Si le tableau panier récupéré de la session n'est pas vide
            if (!empty($panier = $session->get('panier'))) {

                // Pour chaque article contenu dans le tableau du panier
                foreach ($panier as $article) {

                    // On récupère l'objet en BDD ...
                    $objet = $em->getRepository('AAPhotoforyouBundle:Photo')->find($article);

                    // ... et on le met dans un nouveau tableau
                    $articles[] = $objet;   

                    $totalCout = $totalCout + $objet->getPrix();
                }

                // On enlève la valeur null du panier
                unset($panier[array_search(null, $panier)]);
            }

            // Si le coût total est de 0 (donc aucune photo) ...
            if ($totalCout == 0) {

                // ... et s'il n'y a aucun objet dans les articles
                if (!isset($articles)) {

                    // On rajoute "null" pour la liste des articles
                    $articles = null;
                }
            }
        }

        return $this->render('@AAPanier\Panier\panier.html.twig', array(
            'articles' => $articles,
            'totalCout' => $totalCout
        ));
    }

    /**
     * @Route("/panier/ajouter")
     *
     * @Security("has_role('ROLE_CLIENT')")
     */
    public function ajouterAction(Request $request)
    {
    	// On récupère la session
        $session = $request->getSession();

        // Si on peut récupérer la variable de la photo en session
        if ($id = $session->get('id')) {    		

            // Si on peut récupérer le tableau de la session
            if ($panier = $session->get('panier')) {

        		// Pour chaque article contenu dans le tableau
        		foreach ($panier as $article) {

        			// On vérifie si la nouvelle photo n'est pas déjà dans le tableau
        			if($article == $id) {
        				$session->getFlashBag()
                        ->set('info', 'Cette photographie est déjà dans votre panier');

        				return $this->redirectToRoute('photo_voir', array('id' => $id));
        			}
        		}
            }

    		// Si il n'y a aucun problème avant, on ajoute la photo au nouveau tableau
            // Avec son id en tant que clé en plus de la valeur (pour les recherches de clé)
            $panier["$id"] = $id;

            // On met le nouveau tableau dans la session
    		$session->set('panier', $panier);

    		$session->getFlashBag()->set('info', 'Photographie ajoutée au panier');
    	}

        // Si on ne peut pas récupérer l'id de la photo en session
    	else {
    		$request->getSession()->getFlashBag()
            ->set('info', 'Erreur à l\'ajout de la photographie au panier');

    		return $this->redirectToRoute('aa_base_accueil');
    	}

    	return $this->redirectToRoute('photo_voir', array('id' => $id));
    }

    /**
     * @Route("/panier/retirer")
     *
     * @Security("has_role('ROLE_CLIENT')")
     */
    public function retirerAction(Request $request)
    {
        // Si on peut récupérer l'id dans l'URL
        if ($id = $request->query->get('id')) {  

            // Si on peut récupérer la session  
            if ($session = $request->getSession()) {
                $panier = $session->get('panier');

                // On supprime l'id du tableau récupéré de la session
                unset($panier[array_search($id, $panier)]);

                // On met le nouveau tableau dans la session
                $session->set('panier', $panier);

                $session->getFlashBag()
                ->set('info', 'La photographie a bien été supprimée de votre panier');

                return $this->redirectToRoute('panier_voir');
            }
        }

        else {
            $request->getSession()->getFlashBag()
            ->set('info', 'Erreur à la suppression de la photographie du panier');

            return $this->redirectToRoute('aa_base_accueil');
        }

        return $this->redirectToRoute('photo_voir', array('id' => $id));
    }

    /**
     * @Route("/panier/acheter")
     *
     * @Security("has_role('ROLE_CLIENT')")
     */
    public function acheterAction(Request $request)
    {

        // Si on peut récupérer la session
        if ($session = $request->getSession()) {
            $em = $this->getDoctrine()->getManager();
        
            // On récupère l'acheteur
            $acheteur = $this->getUser();

            // On récupère son nombre de crédit
            $credit = $acheteur->getCredit();

            // Si le tableau panier récupéré de la session n'est pas vide
            if (!empty($panier = $session->get('panier'))) {
                foreach ($panier as $article) {
                    $photo = $em->getRepository('AAPhotoforyouBundle:Photo')->find($article);

                    // Si la photo n'as pas déjà été achetée
                    if ($photo->getAcheteur() === null) {

                        $vendeurNom = $photo->getVendeur();

                        $vendeur = $em->getRepository('AAUserBundle:User')
                        ->findOneByUsername($vendeurNom);

                        $prix = $photo->getPrix();

                        if ($prix < $credit) {
                            $acheteur->setCredit($credit - $prix);
                            $vendeur->addCredit($prix / 2);

                            // On définit son nouvel acheteur
                            $photo->setAcheteur($acheteur);

                            // On supprime l'id du tableau récupéré de la session
                            unset($panier[array_search($photo->getId(), $panier)]);

                            // On met le nouveau tableau dans la session
                            $session->set('panier', $panier);

                        }

                        else {
                            $request->getSession()->getFlashBag()
                            ->set('info', 'Vous n\'avez pas assez de crédit sur votre compte');

                            return $this->redirectToRoute('panier_voir');
                        }
                    }

                    else {
                        $request->getSession()->getFlashBag()
                        ->set('info', 'Cette photo a déjà été achetée par un autre client');

                        return $this->redirectToRoute('panier_voir');
                    }
                }

                $em->flush();

                $request->getSession()->getFlashBag()
                ->set('info', 'Achat réussi!');
            }

            else {
                $request->getSession()->getFlashBag()
                ->set('info', 'Il n\'y a aucune photographie dans votre panier');

                return $this->redirectToRoute('panier_voir');
            }
        }

        return $this->redirectToRoute('panier_voir');
    }
}
