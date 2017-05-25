<?php

namespace Rf\AppBundle\Controller\Article;

use Rf\AppBundle\Controller\AbstractController;
use Rf\AppBundle\Doctrine\Entity\Article;

class ViewController extends AbstractController
{
    /**
     * @param Article $article
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Article $article)
    {
        return $this->renderResponse('@AppBundle/article/view.html.twig', [
            'article' => $article,
        ]);
    }
}
