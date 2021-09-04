<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Form\TrickEditType;
use App\Form\TrickType;
use App\Repository\CommentRepository;
use App\Repository\GroupeRepository;
use App\Repository\TrickRepository;
use App\services\Slug;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Exception;
use PhpParser\Node\Stmt\TryCatch;
// use PhpParser\Node\Expr\Cast\String_;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class TrickController extends AbstractController
{
    use TargetPathTrait;

    #[Route('/', name: 'trick_index', methods: ['GET'])]
    public function index(TrickRepository $trickRepository, GroupeRepository $groupeRepository): Response
    {
        $groupes = $groupeRepository->findAll();

        // On récupère les Tricks créées par les utilisateurs pour chaque groupe
        foreach ($groupes as $groupe) {
            $groupesWithTricks[$groupe->getName()] = $trickRepository->findWithAuthor($groupe->getName());
        }


        return $this->render('trick/index.html.twig', [
            'groupes' => $groupesWithTricks
        ]);
    }

    #[Route('/new', name: 'trick_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Slug $slug): Response
    {
        if (!$this->isGranted('ROLE_USER')) {
            $this->addFlash('danger', 'Veuillez vous connecter pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        $trick = new Trick();

        //On attribue le premier groupe à cette figure pour afficher les figures du premier groupe dans le formulaire
        $em = $this->getDoctrine()->getManager();
        $groupe = $em->getRepository(\App\Entity\Groupe::class)->find(1);
        $trick->setGroupe($groupe);

        //Si méthode Post et non Ajax, on récupère la figure correspondante pour la mise à jour (car l'ensemble des figures existent déjà dans la base de données)
        if ($request->getMethod() == 'POST' && !$request->isXmlHttpRequest()) {
            $trickFormData = $request->request->get('trick');
            if (isset($trickFormData['name'])) {
                $trickId = $trickFormData['name'];
                $trick = $em->getRepository(\App\Entity\Trick::class)->find($trickId);
                // Si cette figure possède un auteur, on bloque la modifiation
                if ($trick->getAuthor()) {
                    $this->addFlash('danger', 'Cette figure a déjà été créée par un utilisateur.');
                    return $this->redirectToRoute('trick_new');
                }
            }
        }

        $form = $this->createForm(TrickType::class, $trick, [
            'action' => $this->generateUrl('trick_new'),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick->setCreationDate(new DateTime('now'));
            $trick->setSlug($slug->Slug($trick->getName()));
            $trick->setAuthor($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($trick);
            $entityManager->flush();
            return $this->redirectToRoute('trick_show', ['id' => $trick->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('trick/new.html.twig', [
            'trick' => $trick,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'trick_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Trick $trick): Response
    {
        if (!$this->isGranted('ROLE_USER')) {
            $this->addFlash('danger', 'Veuillez vous connecter pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(TrickEditType::class, $trick);

        //Réindexation du tableau 'images' de la requête avant la validation des données
        $requestFiles = $request->files->get('trick_edit');
        if (isset($requestFiles['images'])) {
            $requestFiles['images'] = array_values($requestFiles['images']);
            $request->files->set('trick_edit', $requestFiles);
        }

        //Réindexation du tableau 'videos' de la requête avant la validation des données
        /**
         * @var Array $requestIframes
         */
        $requestIframes = $request->request->get('trick_edit');
        if (isset($requestIframes['videos'])) {
            $requestIframes['videos'] = array_values($requestIframes['videos']);
            $request->request->set('trick_edit', $requestIframes);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick->setUpdateDate(new DateTime('now'));
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('info', "Les modifications ont bien été enregristrées");
        }

        return $this->renderForm('trick/edit.html.twig', [
            'trick' => $trick,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/new', name: 'trick_edit_home', methods: ['GET', 'POST'])]
    public function newTrick(Request $request, Trick $trick): Response
    {
        if (!$this->isGranted('ROLE_USER')) {
            $this->addFlash('danger', 'Veuillez vous connecter pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(TrickEditType::class, $trick);

        //Réindexation du tableau 'images' de la requête avant la validation des données
        $requestFiles = $request->files->get('trick_edit');
        if (isset($requestFiles['images'])) {
            $requestFiles['images'] = array_values($requestFiles['images']);
            $request->files->set('trick_edit', $requestFiles);
        }

        //Réindexation du tableau 'videos' de la requête avant la validation des données
        /**
         * @var Array $requestIframes
         */
        $requestIframes = $request->request->get('trick_edit');
        if (isset($requestIframes['videos'])) {
            $requestIframes['videos'] = array_values($requestIframes['videos']);
            $request->request->set('trick_edit', $requestIframes);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick->setCreationDate(new DateTime('now'));
            $trick->setAuthor($this->getUser());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('info', "Les modifications ont bien été enregristrées");
        }

        return $this->renderForm('trick/edit.html.twig', [
            'trick' => $trick,
            'form' => $form,
        ]);
    }

    #[Route('/show-{id}', name: 'trick_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Trick $trick = null, FirewallMap $firewallMap): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment, [
            'action' => $this->generateUrl('trick_show', ['id' => $trick->getId()]),
            'method' => 'POST'
        ]);
        $form->handleRequest($request);

        $comments = $this->getDoctrine()
            ->getManager()
            ->getRepository(Comment::class)
            ->findBy(
                array('trick' => $trick),
                array('date' => 'desc')
            );

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY', null, 'Vous devez être connecté pour poster des commentaires.');
            } catch (AccessDeniedException $e) {
                $this->addFlash('danger', $e->getMessage());

                //on récupère le nom du firewall
                $firewallConfig = $firewallMap->getFirewallConfig($request);
                $firewallName = $firewallConfig->getName();

                $this->saveTargetPath($request->getSession(), $firewallName, $request->getUri());
                return $this->redirectToRoute('app_login');
            }
            $comment->setDate(new DateTime('now'));
            $comment->setAuthor($this->getUser());
            $comment->setTrick($trick);
            $comment->setDate(new DateTime('now'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            //Requête Ajax ?
            if ($request->isXmlHttpRequest()) {
                print_r($this->renderView('trick/_comment.html.twig', [
                    'comment' => $comment
                ]));
                die();
            }
            //Sinon
            return $this->redirectToRoute('trick_show', [
                'id' => $trick->getId(),
                'form' => $form
            ], Response::HTTP_SEE_OTHER);
        }

        //Requête Ajax ?
        if ($request->isXmlHttpRequest()) {
            print_r($this->renderView('trick/showAjax.html.twig', [
                'trick' => $trick,
                'comments' => $comments,
                'form' => $form->createView()
            ]));

            die();
        }

        //Sinon
        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
            'comments' => $comments,
            'form' => $form->createView()
        ]);

        $this->container;
    }

    #[Route('/{id}/delete', name: 'trick_delete', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function delete(Request $request, Trick $trick): Response
    {
        $this->denyAccessUnlessGranted('author_delete', $trick, "Vous n'avez pas le droit de supprimer cette figure.");
        if ($this->isCsrfTokenValid('delete' . $trick->getId(), $request->request->get('_token'))) {
            $trick->setAuthor(null);
            $trick->setDescription(null);
            $trick->setCreationDate(null);
            $trick->setUpdateDate(null);
            $trick->getVideos()->clear();
            $trick->getImages()->clear();
            $trick->getComments()->clear();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
        }

        return $this->redirectToRoute('trick_index', [], Response::HTTP_SEE_OTHER);
    }
}
