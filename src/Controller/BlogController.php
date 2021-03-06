<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class BlogController
 * @Route("/blog", name="blog_")
 */
class BlogController extends AbstractController
{
    /**
     * Contrôleur de la partie blog du site. Toutes les routes commenceront leur URL par "/blog/" et leur nom par "blog_"
     *
     * @Route("/nouvelle-publication", name="new_publication")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function newPublication(Request $request): Response
    {

            // Création de l'article
            $newArticle = new Article();

            // Création du formulaire
            $form = $this->createForm(ArticleType::class, $newArticle);
            $form->handleRequest($request);

            // Si le formulaire est ok
            if ($form->isSubmitted() && $form->isValid()) {

                $newArticle
                    ->setAuthor($this->getUser())
                    ->setPublicationDate(new DateTime());

                // On envoie l'article dans la BDD
                $em = $this->getDoctrine()->getManager();
                $em->persist($newArticle);
                $em->flush();

                // Création du message de succès
                $this->addFlash('success', 'L\'article a bien été crée !');

                // Redirection de l'utilisateur sur la page permettant de visualiser le nouvel article
                return $this->redirectToRoute('blog_publication_view', [
                    'slug' => $newArticle->getSlug()
                ]);
            }

        return $this->render('blog/newPublication.html.twig', [
            'articleForm' => $form->createView()
        ]);
    }

    /**
     * Contrôleur de la page qui liste les articles du site
     *
     * @Route("/publications/liste/", name="publication_list")
     */
    public function publicationList(Request $request, PaginatorInterface $paginator): Response
    {

        // On récupère dans l'url, la données GET['page'] (si elle n'existe pas, la valeur par défaut sera "1")
        $requestedPage = $request->query->getInt('page', 1);

        // Si le numéro de page demandé dans l'URL est inferieur à 1, erreur 404
        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        // Récupération du manager général des entités
        $em = $this->getDoctrine()->getManager();

        // Création d'une requête qui servira au paginator pour récuperer les articles de la page courante
        $query = $em->createQuery('SELECT a FROM App\Entity\Article a ORDER BY a.publicationDate DESC');

        // On stocke dans $articles les 10 articles de la page demandée dans l'url
        $articles = $paginator->paginate(
            $query,             // Requête de selection
            $requestedPage,     // Numéro de la page actuelle
            10                  // Nombre d'articles par page
        );

        // Appel de la vue en lui envoyant la liste des articles
        return $this->render('blog/publicationList.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * Contrôleur de la page d'un article en détail
     *
     * @Route("/publication/{slug}/", name="publication_view")
     */
    public function publicationView(Article $article, Request $request): Response
    {

        // Si l'utilisateur n'est pas connecté, on appel directement la vue sans traiter le formulaire en dessous
        if(!$this->getUser()){
            return $this->render('blog/publicationView.html.twig', [
                'article' => $article,
            ]);
        }

        // Création du formulaire de création de commentaire
        $newComment = new Comment();

        $form = $this->createForm(CommentType::class, $newComment);

        $form->handleRequest($request);

        // Si le formulaire a été envoyé et ne contient pas d'erreur
        if($form->isSubmitted() && $form->isValid()){

            // On termine d'hydrater le commentaire
            $newComment
                ->setPublicationDate( new DateTime() )
                ->setArticle( $article )
                ->setAuthor( $this->getUser() )
            ;

            // Sauvegarde du commentaire dans la BDD
            $em = $this->getDoctrine()->getManager();
            $em->persist($newComment);
            $em->flush();

            // Message flash de succès
            $this->addFlash('success', 'Votre commentaire a été publié avec succès !');

            // Suppression des deux variables du formulaire et du commentaire nouvellement créé pour éviter que le nouveau formulaire soit rempli après la création
            unset($newComment);
            unset($form);

            $newComment = new Comment();
            $form = $this->createForm(CommentType::class, $newComment);

        }

        return $this->render('blog/publicationView.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Page admin permettant de modifier un article existant
     * @Route("/publication/modifier/{id}/", name="publication_edit")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function publicationEdit(Article $article, Request $request): Response
    {

        // Création du formulaire de modification d'article (c'est le même formulaire que celui de la page de création d'un article, sauf qu'il sera déja
        // rempli avec les données de l'article "$article")
        $form = $this->createForm(ArticleType::class, $article);

        // Liaison des données POST avec le formulaire
        $form->handleRequest($request);

        // Si le formulaire est envoyé et n'as pas d'erreur
        if($form->isSubmitted() && $form->isValid()){

            // Sauvegarde des changements dans la BDD
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // Message flash de succès
            $this->addFlash('success', 'Article modifié avec succès !');

            // Redirection vers la page de l'article modifié
            return $this->redirectToRoute('blog_publication_view', [
                'slug' => $article->getSlug(),
            ]);
        }
        // Appel de la vue en envoyant le formulaire à afficher
        return $this->render('blog/publicationEdit.html.twig', [
            'articleForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("publication/suppression/{id}", name="publication_delete")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function publicationDelete(Article $article, Request $request): Response
    {
        // Récupération du token csrf dans l'url
        $tokenCSRF = $request->query->get('csrf_token');

        if(!$this->isCsrfTokenValid('blog_publication_delete_' . $article->getId(), $tokenCSRF)){
            $this->addFlash('error', 'Token sécurité invalide, veuillez ré-essayer.');
        } else {

            // Suppression de l'article
            $em = $this->getDoctrine()->getManager();
            $em->remove($article);
            $em->flush();

            $this->addFlash('success', 'La publication a été supprimée avec succès !');

        }

        return $this->redirectToRoute('blog_publication_list');
    }

    /**
     * Page admin permettant de supprimer un commentaire
     *
     * @Route("/commentaire/suppression/{id}/", name="comment_delete")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function commentDelete(Comment $comment, Request $request): Response
    {

        // Récupération du token csrf dans l'url
        $tokenCSRF = $request->query->get('csrf_token');

        // Vérification que le token est valide
        if(!$this->isCsrfTokenValid(
            'blog_comment_delete_' . $comment->getId(),
            $tokenCSRF
        )){
            $this->addFlash('error', 'Token sécurité invalide, veuillez ré-essayer.');
        } else {

            // Suppression du commentaire
            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);
            $em->flush();

            $this->addFlash('success', 'Le commentaire a été supprimé avec succès !');

        }
        return $this->redirectToRoute('blog_publication_view', [
            'slug' => $comment->getArticle()->getSlug(),
        ]);
    }

    /**
     * Page qui affiche les résulat de recherche du formulaire dans la navbar
     * @Route("/recherche/", name="search")
     */
    public function search(Request $request, PaginatorInterface $paginator): Response
    {

        // On récupère dans l'url, la données GET['page'] (si elle n'existe pas, la valeur par défaut sera "1")
        $requestedPage = $request->query->getInt('page', 1);

        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }
        $em = $this->getDoctrine()->getManager();

        // Récupération de la recherche dans le formulaire
        $search = $request->query->get('q');

        // Création de la requête (préparée pour éviter les injections SQL)
        $query = $em
            ->createQuery('SELECT a FROM App\Entity\Article a WHERE a.title LIKE :search OR a.content LIKE :search ORDER BY a.publicationDate DESC')
            ->setParameters(['search' => '%' . $search . '%'])
        ;

        // Récupération des articles
        $articles = $paginator->paginate(
            $query,
            $requestedPage,
            15
        );

        return $this->render('blog/search.html.twig', [
            'articles' => $articles,
        ]);
    }


}
