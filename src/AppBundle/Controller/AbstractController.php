<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Controller;

use Rf\AppBundle\Component\HttpFoundation\Response\Response;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractController
{
    /**
     * @var TwigEngine
     */
    private $twig;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param TwigEngine      $twig
     * @param RouterInterface $router
     */
    public function __construct(TwigEngine $twig, RouterInterface $router)
    {
        $this->twig = $twig;
        $this->router = $router;
    }

    /**
     * @param string $route
     * @param array  $parameters
     *
     * @return string
     */
    protected function render(string $route, array $parameters = []): string
    {
        return $this->twig->render($route, $parameters);
    }

    /**
     * @param string            $route
     * @param array             $parameters
     * @param BaseResponse|null $response
     *
     * @return BaseResponse|Response
     */
    protected function renderResponse(string $route, array $parameters = [], BaseResponse $response = null): BaseResponse
    {
        return $this->twig->renderResponse($route, $parameters, $response);
    }

    /**
     * @param string   $content
     * @param int|null $status
     * @param array    $headers
     *
     * @return Response
     */
    protected function response(string $content, int $status = null, array $headers = []): Response
    {
        return new Response($content, $status ?: 200, $headers);
    }

    /**
     * @param string   $url
     * @param int|null $status
     * @param array    $headers
     *
     * @return RedirectResponse
     */
    protected function redirect(string $url, int $status = null, array $headers = []): RedirectResponse
    {
        return new RedirectResponse($url, $status ?: 302, $headers);
    }

    /**
     * @param string $url
     * @param array  $headers
     *
     * @return RedirectResponse
     */
    protected function redirectTemporary(string $url, array $headers = []): RedirectResponse
    {
        return $this->redirect($url, 302, $headers);
    }

    /**
     * @param string $url
     * @param array  $headers
     *
     * @return RedirectResponse
     */
    protected function redirectPermanent(string $url, array $headers = []): RedirectResponse
    {
        return $this->redirect($url, 301, $headers);
    }

    /**
     * @param string  $name
     * @param mixed[] $parameters
     *
     * @return string
     */
    protected function route(string $name, array $parameters = []): string
    {
        return $this->router->generate($name, $parameters);
    }
}
