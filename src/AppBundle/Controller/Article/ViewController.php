<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

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
