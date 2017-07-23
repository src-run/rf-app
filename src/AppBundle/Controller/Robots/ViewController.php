<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Controller\Robots;

use Rf\AppBundle\Component\HttpFoundation\Response\TextResponse;
use Rf\AppBundle\Component\Registry\Robots\RobotsRegistry;
use Rf\AppBundle\Controller\AbstractController;

class ViewController extends AbstractController
{
    /**
     * @param RobotsRegistry $robotsRegistry
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(RobotsRegistry $robotsRegistry)
    {
        return $this->renderResponse('@AppBundle/robots/view.txt.twig', [
            'robots_registry' => $robotsRegistry,
        ], new TextResponse());
    }
}
