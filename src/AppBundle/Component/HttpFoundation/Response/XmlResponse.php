<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\HttpFoundation\Response;

class XmlResponse extends Response
{
    /**
     * @param string $content
     * @param int    $status
     * @param array  $headers
     */
    public function __construct(string $content = '', int $status = 200, array $headers = [])
    {
        parent::__construct($content, $status, $headers);

        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', 'application/xml');
        }
    }
}
