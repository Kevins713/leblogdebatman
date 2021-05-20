<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

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
    public function newPublication(): Response
    {
        return $this->render('blog/newPublication.html.twig');
    }
}
