<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Repository\ArticleRepository;

class MainController extends AbstractController
{
    /**
     * Contrôleur de la page d'accueil du site
     * @Route("/", name="main_home")
     */
    public function home(): Response
    {

        $articleRepo = $this->getDoctrine()->getRepository(Article::class);

        // Récupération du paramètre pour savoir combien d'article seront affichés sur l'accueil
        $articlesNumber = $this->getParameter('homepage_articles_number');


        // Récupération des derniers articles publiés
        $articles = $articleRepo->findBy([], ['publicationDate' => 'DESC'], $articlesNumber);

        return $this->render('main/index.html.twig',[
            'articles' => $articles,
        ]);
    }
}
