<?php

namespace Rf\AppBundle\Controller\Redirect;

use Rf\AppBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class RemoveTrailingSlashController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        return $this->redirectPermanent(
            str_replace($request->getPathInfo(), rtrim($request->getPathInfo(), ' /'), $request->getRequestUri())
        );
    }
}
