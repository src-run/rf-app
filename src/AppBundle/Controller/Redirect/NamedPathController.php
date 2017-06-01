<?php

namespace Rf\AppBundle\Controller\Redirect;

use Rf\AppBundle\Component\HttpFoundation\Request\RequestAttributesResolver;
use Rf\AppBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

class NamedPathController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        if (!($resolver = new RequestAttributesResolver($request))->has(['_redirect_path'])) {
            throw new MissingMandatoryParametersException('The "_redirect_path" route default must be set to a uri.');
        }

        list($path, $type) = $resolver->get([
            '_redirect_path',
            '_redirect_type',
        ]);

        return $this->redirect($path, $type);
    }
}
