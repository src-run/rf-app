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

use Knp\Component\Pager\Paginator;
use Rf\AppBundle\Controller\AbstractController;
use Rf\AppBundle\Doctrine\Repository\ArticleRepository;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Routing\RouterInterface;

class ApiController extends AbstractController
{
    /**
     * @var ArticleRepository
     */
    private $repository;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @param TwigEngine        $twig
     * @param RouterInterface   $router
     * @param ArticleRepository $repository
     * @param Paginator         $pager
     */
    public function __construct(TwigEngine $twig, RouterInterface $router, ArticleRepository $repository, Paginator $pager)
    {
        parent::__construct($twig, $router);

        $this->repository = $repository;
        $this->paginator = $pager;
    }
}
