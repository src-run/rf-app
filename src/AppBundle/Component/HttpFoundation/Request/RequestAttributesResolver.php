<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class RequestAttributesResolver
{
    /**
     * @var ParameterBag
     */
    private $attributes;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->attributes = $request->attributes;
    }

    /**
     * @param string[] ...$fields
     *
     * @return bool
     */
    public function has(array $fields = []): bool
    {
        foreach ($fields as $field) {
            if (!$this->attributes->has($field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string[] ...$fields
     *
     * @return array
     */
    public function get(array $fields = []): array
    {
        return array_map(function (string $field) {
            return $this->attributes->has($field) ? $this->attributes->get($field) : null;
        }, $fields);
    }
}
