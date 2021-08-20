<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use App\services\Slug;
use DateTime;
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
    public function index(TrickRepository $trickRepository): Response
    {
        return $this->render('trick/index.html.twig', [
            'tricks' => $trickRepository->findAll(),
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
        $form = $this->createForm(TrickType::class, $trick);
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

        $this->denyAccessUnlessGranted('author_edit', $trick, "Vous n'avez pas le droit de modifier cette figure.");

        $form = $this->createForm(TrickType::class, $trick);
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

    #[Route('/{id}/delete', name: 'trick_delete', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function delete(Request $request, Trick $trick): Response
    {
        $this->denyAccessUnlessGranted('author_delete', $trick, "Vous n'avez pas le droit de supprimer cette figure.");

        if ($this->isCsrfTokenValid('delete' . $trick->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($trick);
            $entityManager->flush();
        }

        return $this->redirectToRoute('trick_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/show-{id}', name: 'trick_show', methods: ['GET', 'POST'])]
    public function test(Request $request, Trick $trick = null, FirewallMap $firewallMap): Response
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
                echo $this->renderView('trick/_comment.html.twig', [
                    'comment' => $comment
                ]);
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

            echo $this->renderView('trick/showAjax.html.twig', [
                'trick' => $trick,
                'comments' => $comments,
                'form' => $form->createView()
            ]);

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
}
