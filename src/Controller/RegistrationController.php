<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\services\RandomStrGenerator;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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

class RegistrationController extends AbstractController
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/registration', name: 'registration')]
    public function index(Request $request, MailerInterface $mailer, BodyRendererInterface $bodyRenderer, RandomStrGenerator $randomStrGenerator): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
            $user->setRegistrationDate(new DateTime('now'));
            $user->setRoles(['ROLE_USER']);
            $user->setConfirmationToken($randomStrGenerator->generator());
            $user->setConfirmed(False);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // generate a signed url and email it to the user

            $email = (new TemplatedEmail())
                ->from(new Address('blascaze@aol.com', 'Benoît'))
                // ->to($user->getEmail())
                ->to('blascaze@aol.com')
                ->subject('SnowTricks : veuillez confirmez votre email')

                // path of the Twig template to render
                ->htmlTemplate('registration/confirmation_email.html.twig')

                // pass variables (name => value) to the template
                ->context([
                    'url' =>  $this->generateUrl('activation', ['id' => $user->getId(), 'token' => $user->getConfirmationToken()], UrlGeneratorInterface::ABSOLUTE_URL)
                ]);

            $bodyRenderer->render($email);

            $mailer->send($email);

            $this->addFlash('info', 'Merci, un mail vous a été envoyé pour confirmer votre compte');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @ParamConverter("user", options={"mapping": {"id": "id", "token": "confirmationToken"}})
     */
    #[Route('/activation-{id}-{token}', name: 'activation', requirements: ['id' => '\d+', 'token' => '[0-9a-zA-Z]+'])]
    public function verifyUserEmail($token, $id, User $user = null)
    {
        if ($user) {
            $user->setConfirmationToken(null);
            $user->setConfirmed(True);
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('info', 'Merci, votre compte est maintenat activé');
        } else {
            $this->addFlash('danger', 'Ce lien d\'activation est invalide');
        }

        return $this->redirectToRoute('app_login');
    }

    /**
     * @IsGranted("ROLE_USER")
     * @ParamConverter("user")
     */
    #[Route('/@{name}-{id}', name: 'account', requirements: ['id' => '\d+'])]
    public function accountModification(Request $request, User $user = null)
    {
        if ($user) {
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
                $em = $this->getDoctrine()->getManager();
                $em->flush();

                $this->addFlash('info', 'Votre compte a bien été modifié');
            }
        }
        return $this->render('registration/account.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
