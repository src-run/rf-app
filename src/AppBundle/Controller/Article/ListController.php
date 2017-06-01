<?php

namespace Rf\AppBundle\Controller\Article;

use Knp\Component\Pager\Paginator;
use Rf\AppBundle\Controller\AbstractController;
use Rf\AppBundle\Doctrine\Repository\ArticleRepository;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class ListController extends AbstractController
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

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request)
    {
        return $this->renderResponse('@AppBundle/article/list.html.twig', [
            'articles' => $this->repository->findPaginated($this->paginator, $request->query->getInt('page', 1), 10),
        ]);
    }
}
