<?php

namespace Rf\AppBundle\Controller;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController
{
    /**
     * @var TwigEngine
     */
    private $twigEngine;

    /**
     * @param TwigEngine $twigEngine
     */
    public function __construct(TwigEngine $twigEngine)
    {
        $this->twigEngine = $twigEngine;
    }

    /**
     * @param string $route
     * @param array  $parameters
     *
     * @return string
     */
    protected function renderView(string $route, array $parameters = [])
    {
        return $this->twigEngine->render($route, $parameters);
    }

    /**
     * @param string        $route
     * @param array         $parameters
     * @param Response|null $response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderResponse(string $route, array $parameters = [], Response $response = null)
    {
        return $this->twigEngine->renderResponse($route, $parameters, $response);
    }
}
