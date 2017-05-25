<?php

namespace Rf\AppBundle\Controller\Index;

use Rf\AppBundle\Controller\AbstractController;

class ViewController extends AbstractController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke()
    {
        return $this->renderResponse('@AppBundle/index/view.html.twig');
    }
}
