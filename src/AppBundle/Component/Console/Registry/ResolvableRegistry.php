<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Console\Registry;

use Rf\AppBundle\Component\Console\ParameterResolver;
use SR\Exception\Logic\InvalidArgumentException;

class ResolvableRegistry extends AbstractRegistry
{
    /**
     * @var ParameterResolver
     */
    private $parameterResolver;

    /**
     * @param ParameterResolver $parameterResolver
     * @param array             $elements
     */
    public function __construct(ParameterResolver $parameterResolver, array $elements = [])
    {
        $this->parameterResolver = $parameterResolver;

        parent::__construct($elements, true);
    }

    /**
     * @param string $name
     * @param string $key
     *
     * @return AbstractRegistry
     */
    public function resolve(string $name, string $key): AbstractRegistry
    {
        return $this->set($name, $this->doParameterResolution($key));
    }

    /**
     * @param string $name
     * @param string $key
     *
     * @return AbstractRegistry
     */
    public function resolveRealPath(string $name, string $key): AbstractRegistry
    {
        return $this->setRealPath($name, $this->doParameterResolution($key));
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    private function doParameterResolution(string $name)
    {
        if (false === $value = $this->parameterResolver->resolve($name)) {
            throw new InvalidArgumentException('Unable to resolve valid value for "%s" argument/option.', $name);
        }

        return $value;
    }
}
