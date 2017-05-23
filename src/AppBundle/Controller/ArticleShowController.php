<?php

namespace Rf\AppBundle\Controller;

use Rf\AppBundle\Doctrine\Entity\Article;

class ArticleShowController extends AbstractController
{
    /**
     * @param Article $article
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Article $article)
    {
        return $this->renderResponse('@AppBundle/article/show.html.twig', [
            'article' => $article,
        ]);
    }
}
