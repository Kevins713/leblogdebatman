<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use DateTime;

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
                return $this->redirectToRoute('main_home');
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
    public function publicationList(): Response
    {

        // Récupération du repository des articles pour pouvoir les récupérer
        $articleRepo = $this->getDoctrine()->getRepository(Article::class);

        $articles = $articleRepo->findAll();

        // Appel de la vue en lui envoyant la liste des articles
        return $this->render('blog/publicationList.html.twig', [
            'articles' => $articles,
        ]);
    }
}
