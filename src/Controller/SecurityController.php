<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPassType;
use App\Form\UserResetPassType;
use App\Repository\UserRepository;
use App\services\RandomStrGenerator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class SecurityController extends AbstractController
{

    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('trick_index');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/oubli-pass", name="app_forgotten_password")
     */
    public function oubliPass(Request $request, UserRepository $users, MailerInterface $mailer, BodyRendererInterface $bodyRenderer, RandomStrGenerator $randomStrGenerator): Response
    {
        // On initialise le formulaire
        $form = $this->createForm(ResetPassType::class);

        // On traite le formulaire
        $form->handleRequest($request);

        // Si le formulaire est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère les données
            $donnees = $form->getData();

            // On cherche un utilisateur ayant cet e-mail
            $user = $users->findOneByEmail($donnees['email']);

            // Si l'utilisateur n'existe pas
            if ($user === null) {
                // On envoie une alerte disant que l'adresse e-mail est inconnue
                $this->addFlash('danger', 'Cette adresse e-mail est inconnue');

                // On retourne sur la page de connexion
                return $this->redirectToRoute('app_login');
            }

            // On génère un token
            $token = $randomStrGenerator->generator();

            // On essaie d'écrire le token en base de données
            try {
                $user->setReseToken($token);
                $user->setResetAt(new \DateTime('NOW'));
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();
                // On génère l'URL de réinitialisation de mot de passe
                $url = $this->generateUrl('app_reset_password', array('token' => $token, 'id' => $user->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('app_login');
            }

            // On génère l'e-mail
            $email = (new TemplatedEmail())
                ->from(new Address('blascaze@aol.com', 'Benoît'))
                ->to($user->getEmail())
                ->subject('SnowTricks : réinitialisation de votre mot de passe')

                // path of the Twig template to render
                ->htmlTemplate('security/reset_password_email.html.twig')

                // pass variables (name => value) to the template
                ->context([
                    'url' =>  $url
                ]);

            $bodyRenderer->render($email);

            $mailer->send($email);

            $this->addFlash('info', 'Merci, un mail vous a été envoyé pour confirmer votre compte');

            // On redirige vers la page de login
            return $this->redirectToRoute('app_login');
        }

        // On envoie le formulaire à la vue
        return $this->render('security/forgotten_password.html.twig', ['emailForm' => $form->createView()]);
    }

    /**
     * @Route("/reset_pass/{token}-{id}", name="app_reset_password")
     * @ParamConverter("user", options={"mapping": {"id": "id", "token": "reseToken"}})
     */
    public function resetPassword(Request $request, User $user = null)
    {
        // Si l'utilisateur n'existe pas
        if ($user === null) {
            // On affiche une erreur
            $this->addFlash('danger', 'Lien de réinitialisation du mot de passe invalide');
            return $this->redirectToRoute('app_login');
        }

        // On initialise le formulaire
        $form = $this->createForm(UserResetPassType::class, $user);

        // On traite le formulaire
        $form->handleRequest($request);

        // Si le formulaire est valide
        if ($form->isSubmitted() && $form->isValid()) {

            $now = new \DateTime('NOW');
            if ($now->diff($user->getResetAt())->days < 2) {
                $user->setReseToken(null);

                // On chiffre le mot de passe
                $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));

                // On stocke
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();

                $this->addFlash('info', 'Votre mot de passe a bien été mis à jour');
                return $this->redirectToRoute('app_login');
            }
            // On affiche une erreur
            $this->addFlash('danger', "Ce lien de réinitialisation du mot de passe n'est plus valide");
            return $this->redirectToRoute('app_login');
        }

        // On envoie le formulaire à la vue
        return $this->render('security/reset_password.html.twig', ['passForm' => $form->createView()]);
    }
}
