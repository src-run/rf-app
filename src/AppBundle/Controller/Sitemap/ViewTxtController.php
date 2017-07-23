<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Controller\Sitemap;

use Rf\AppBundle\Component\HttpFoundation\Response\TextResponse;
use Rf\AppBundle\Component\Sitemap\RecordGenerator;
use Rf\AppBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ViewTxtController extends AbstractController
{
    /**
     * @param RecordGenerator $generator
     *
     * @return Response
     */
    public function __invoke(RecordGenerator $generator): Response
    {
        return $this->renderResponse('@AppBundle/sitemap/view.txt.twig', [
            'uri_definitions' => $generator->generate(),
        ], new TextResponse());
    }
}
