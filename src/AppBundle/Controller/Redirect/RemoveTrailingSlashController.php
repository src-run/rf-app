<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

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
