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

use Rf\AppBundle\Component\HttpFoundation\Request\RequestAttributesResolver;
use Rf\AppBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

class NamedRouteController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        if (!($resolver = new RequestAttributesResolver($request))->has(['_redirect_name'])) {
            throw new MissingMandatoryParametersException('The "_redirect_name" route default must be set to a route name.');
        }

        list($name, $args, $type) = $resolver->get([
            '_redirect_name',
            '_redirect_args',
            '_redirect_type',
        ]);

        return $this->redirect($this->route($name, $args ?: []), $type);
    }
}
