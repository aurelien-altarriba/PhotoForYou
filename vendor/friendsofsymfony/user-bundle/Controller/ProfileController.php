<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\UserBundle\Controller;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

// Ajoutés manuellement
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AA\PhotoforyouBundle\Entity\Photo;

/**
 * Controller managing the user profile.
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class ProfileController extends Controller
{
  private $eventDispatcher;
  private $formFactory;
  private $userManager;

  public function __construct(EventDispatcherInterface $eventDispatcher, FactoryInterface $formFactory, UserManagerInterface $userManager)
  {
      $this->eventDispatcher = $eventDispatcher;
      $this->formFactory = $formFactory;
      $this->userManager = $userManager;
  }

  /**
   * Show the user.
   */
  public function showAction()
  {
      $user = $this->getUser();
      if (!is_object($user) || !$user instanceof UserInterface) {
          throw new AccessDeniedException('Cet utilisateur n\'a pas accès à cette section.');
      }

      return $this->render('@FOSUser/Profile/show.html.twig', array(
          'user' => $user,
      ));
  }

  /**
   * Edit the user.
   *
   * @param Request $request
   *
   * @return Response
   */
  public function editAction(Request $request)
  {
      $user = $this->getUser();
      if (!is_object($user) || !$user instanceof UserInterface) {
          throw new AccessDeniedException('Cet utilisateur n\'a pas accès à cette section.');
      }

      $event = new GetResponseUserEvent($user, $request);
      $this->eventDispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

      if (null !== $event->getResponse()) {
          return $event->getResponse();
      }

      $form = $this->formFactory->createForm();
      $form->setData($user);

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
          $event = new FormEvent($form, $request);
          $this->eventDispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

          $this->userManager->updateUser($user);

          if (null === $response = $event->getResponse()) {
              $url = $this->generateUrl('fos_user_profile_show');
              $response = new RedirectResponse($url);
          }

          $this->eventDispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

          return $response;
      }

      return $this->render('@FOSUser/Profile/edit.html.twig', array(
          'form' => $form->createView(),
      ));
  }

  /**
   * @Security("has_role('ROLE_CLIENT')")
   */
  public function rechargerAction(Request $request)
  {
    // On crée un formulaire avec le FormBuilder
    $formBuilder = $this->get('form.factory')->createBuilder(FormType::class);

    // On ajoute les champs de l'entité que l'on veut à notre formulaire
    $formBuilder
      ->add('nbCredit', IntegerType::class, array(
          'attr' => array(
              'value' => 1,
              'min'   => 1,
              'max'   => 1000,
              'label' => 'Nombre de crédit à recharger :'
          )
      ))
      ->add('Acheter', SubmitType::class)
    ;

    // À partir du formBuilder, on génère le formulaire
    $form = $formBuilder->getForm();

    $form->handleRequest($request);

    // Si le formulaire est soumis
    if ($form->isSubmitted() && $form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $data = $form->getData();

      // On récupère le nombre de crédit * 5 pour le prix (1 crédit = 5€)
      $nbCredit = $data['nbCredit'];
      $prix = $nbCredit * 5;

      // On récupère l'id de l'objet User
      $user = $this->getUser();

      // On envoie l'objet à Doctrine
      $em->persist($user);

      // On ajoute le crédit à l'utilisateur
      $user->addCredit($nbCredit);

      // On exécute le changement en BDD
      $em->flush();

      // On défini un message pour confirmer l'ajout
      $message = "Votre achat de $nbCredit crédit(s) au prix de $prix € a été effectué, merci!";
      $request->getSession()->getFlashBag()
      ->set('info', $message);
    }

    return $this->render('@FOSUser/Profile/recharger.html.twig', array(
        'form' => $form->createView()
    ));
  }

  public function desinscriptionAction(Request $request)
  {
    // On récupère l'objet User de l'utilisateur connecté
    $user = $this->getUser();

    $em = $this->getDoctrine()->getManager();
    
    // Si l'utilisateur est un photographe
    if($user->getPhotographe()) {

      // On récupère toutes ses photos en vente ...
      $listPhotos = $em->getRepository('AA\PhotoforyouBundle\Entity\Photo')->getMesPhotosEnVente($user->getUsername());

      // ... et on supprime ces photos invendues
      foreach($listPhotos as $photo) {
        $em->remove($photo);
      }
    }

    // On supprime l'utilisateur actuel
    $em->remove($user);

    // On confirme la/les suppression(s) en BDD
    $em->flush();

    // On détruit les variables de session pour déconnecter l'utilisateur
    unset($_SESSION['_sf2_attributes']);

    $request->getSession()->getFlashBag()
    ->set('info', 'Votre compte a bien été supprimé, nous espérons vous revoir bientôt!');

    return $this->redirectToRoute('aa_base_accueil');
  }

  /**
   * @Security("has_role('ROLE_PHOTOGRAPHE')")
   */
  public function retirerAction(Request $request)
  {
    // On récupère l'id de l'objet User
    $user = $this->getUser();

    $credit = $user->getCredit();

    $argent = $credit * 5;

    // On crée un formulaire avec le FormBuilder
    $formBuilder = $this->get('form.factory')->createBuilder(FormType::class);

    // On ajoute les champs de l'entité que l'on veut à notre formulaire
    $formBuilder
      ->add('retirer', SubmitType::class, array(
        'label' => "Retirer mes $credit credits ($argent €)"
      ))
    ;

    // À partir du formBuilder, on génère le formulaire
    $form = $formBuilder->getForm();

    $form->handleRequest($request);

    // Si le formulaire est soumis
    if ($form->isSubmitted() && $form->isValid()) {
      $em = $this->getDoctrine()->getManager();

      // On envoie l'objet à Doctrine
      $em->persist($user);

      // On ajoute le crédit à l'utilisateur
      $user->setCredit(0);

      // On exécute le changement en BDD
      $em->flush();

      // On défini un message pour confirmer l'ajout
      $message = "Vous avez retiré vos $credit crédits. $argent € a été envoyé sur votre compte!";
      $request->getSession()->getFlashBag()
      ->set('info', $message);
    }

    return $this->render('@FOSUser/Profile/retirer.html.twig', array(
        'form' => $form->createView()
    ));
  }
}
