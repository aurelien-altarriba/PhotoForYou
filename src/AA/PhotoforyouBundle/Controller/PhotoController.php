<?php

namespace AA\PhotoforyouBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AA\PhotoforyouBundle\Entity\Photo;
use AA\PhotoforyouBundle\Form\PhotoType;
use AA\PhotoforyouBundle\Form\PhotoModifierType;
use AA\UserBundle\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PhotoController extends Controller
{
    /**
     * @Route("/catalogue/{page}")
     */
    public function catalogueAction($page, Request $request)
    {
        if ($page < 1) {
            $page = 1;
        }

        if ($session = $request->getSession()) {
            $nbPerPage = $session->get('nbPerPage');
        }

        if(!isset($nbPerPage)) {
            $nbPerPage = 5;
        }

        // On crée le FormBuilder grâce au service form factory
        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class);

        // On ajoute les champs de l'entité que l'on veut à notre formulaire
        $formBuilder
          ->add('nbPerPage', IntegerType::class, array(
              'attr' => array(
                  'min'   => 5,
                  'max'   => 25,
                  'step'  => 5
              )
          ))
          ->add('categories', EntityType::class, array(
              'class'        => 'AAPhotoforyouBundle:Categorie',
              'choice_label' => 'name',
              'multiple'     => false
           ))
          ->add('Actualiser', SubmitType::class)
        ;

        // À partir du formBuilder, on génère le formulaire
        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        $categorie = "Aucune";

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $nbPerPage = $data['nbPerPage'];
            $categorie = $data['categories']->getName();
            
            $session->set('nbPerPage', $nbPerPage);
            $session->set('categorie', $categorie);
        }

        if($categorie == "Aucune") {
            $listPhotos = $this->getDoctrine()
              ->getManager()
              ->getRepository('AAPhotoforyouBundle:Photo')
              ->getPhotosCatalogue($page, $nbPerPage)
            ;   
        } else {
            $listPhotos = $this->getDoctrine()
              ->getManager()
              ->getRepository('AAPhotoforyouBundle:Photo')
              ->getPhotosCatalogueAvecCategorie($page, $nbPerPage, $categorie)
            ;  
        }

        // On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
        if($nbPages = ceil(count($listPhotos) / $nbPerPage)) {
            // Si la page n'existe pas, on retourne une 404
            if ($page > $nbPages) {
                throw $this->createNotFoundException("La page ".$page." n'existe pas.");
            }    
        }

        // On donne toutes les informations nécessaires à la vue
        return $this->render('@AAPhotoforyou\Photo\catalogue.html.twig', array(
          'listPhotos'  => $listPhotos,
          'nbPages'     => $nbPages,
          'page'        => $page,
          'nbPerPage'   => $nbPerPage,
          'categorie'   => $categorie,
          'form' => $form->createView()
        ));
    }

    /**
     * @Route("/ajouter")
     *
     * @Security("has_role('ROLE_PHOTOGRAPHE')")
     */
    public function ajouterAction(Request $request)
    {
        $photo = new Photo();
        
        $form = $this->createForm(PhotoType::class, $photo);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $user = $this->getUser();
            $photo->setVendeur($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($photo);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', 'Votre photographie a bien été ajoutée au catalogue de vente');

            return $this->redirectToRoute('photo_voir', array('id' => $photo->getId()));
        }

        // Si le formulaire n'est pas valide
        return $this->render('@AAPhotoforyou/Photo/ajouter.html.twig', array(
          'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/modifier")
     *
     * @Security("has_role('ROLE_PHOTOGRAPHE')")
     */
    public function modifierAction($id, Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $photo = $em->getRepository('AAPhotoforyouBundle:Photo')->find($id);

        if (null === $photo) {
          throw new NotFoundHttpException("La photo d'id ".$id." n'existe pas.");
        }

        $form = $this->createForm(PhotoModifierType::class, $photo);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($photo);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', 'Votre photographie a bien été modifiée');

            return $this->redirectToRoute('photo_voir', array('id' => $photo->getId()));
        }

        return $this->render('@AAPhotoforyou\Photo\modifier.html.twig', array(
          'form' => $form->createView(),
          'photo' => $photo
        ));
    }

    /**
     * @Route("supprimer")
     *
     * @Security("has_role('ROLE_PHOTOGRAPHE')")
     */
    public function supprimerAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $photo = $em->getRepository('AAPhotoforyouBundle:Photo')->find($id);

        if (null === $photo) {
          throw new NotFoundHttpException("La photo d'id ".$id." n'existe pas.");
        }

        // On boucle sur les catégories de la photo pour les supprimer
        foreach ($photo->getCategories() as $categorie) {
          $photo->removeCategorie($categorie);
        }

        $em->flush();

        return $this->render('@AAPhotoforyou\Photo\supprimer.html.twig');
    }

    /**
     * @Route("/photo/{id}")
     */
    public function photoAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        // Pour récupérer une seule photo, on utilise la méthode find($id)
        $photo = $em->getRepository('AAPhotoforyouBundle:Photo')->find($id);

        // null si l'id $id n'existe pas
        if (null === $photo) {
          throw new NotFoundHttpException("La photo d'id ".$id." n'existe pas.");
        }

        $session = $request->getSession();
        $session->set('id', $photo->getId());

        // Si ce n'est pas un photographe ou un administrateur, on incrémente la vue
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_PHOTOGRAPHE')) {
            $photo->upNbVues(); 
            $em->flush();
        }

        return $this->render('@AAPhotoforyou/Photo/photo.html.twig', array(
            'photo' => $photo,
            'idUser' => $this->getUser()
        ));
    }

    /**
     * @Route("/mes-photos")
     *
     * @Security("has_role('ROLE_CLIENT')")
     */
    public function mesphotosAction(Request $request)
    {
        // On récupère l'ID de l'utilisateur
        $nomUser = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        // On récupère les photos ayant son nom en tant qu'acheteur
        $listPhotos = $em->getRepository('AAPhotoforyouBundle:Photo')->getMesPhotos($nomUser);

        return $this->render('@AAPhotoforyou/Photo/mesphotos.html.twig', array(
            'listPhotos'  => $listPhotos
        ));
    }

    /**
     * @Route("/photos-en-vente")
     *
     * @Security("has_role('ROLE_PHOTOGRAPHE')")
     */
    public function photosEnVenteAction(Request $request)
    {
      // On récupère le nom de l'utilisateur
      $nomUser = $this->getUser()->getUsername();

      $em = $this->getDoctrine()->getManager();

      // On récupère les photos ayant son nom en tant qu'acheteur
      $listPhotos = $em->getRepository('AAPhotoforyouBundle:Photo')->getMesPhotosEnVente($nomUser);

      return $this->render('@AAPhotoforyou/Photo/photosEnVente.html.twig', array(
          'listPhotos'  => $listPhotos
      ));
    }
}
