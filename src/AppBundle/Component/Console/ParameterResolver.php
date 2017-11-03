<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Console;

use Rf\AppBundle\Component\DependencyInjection\ParameterResolver as DependencyInjectionParameterResolver;
use SR\Console\Output\Style\StyleAwareTrait;
use SR\Console\Output\Style\StyleInterface;
use SR\Exception\Runtime\RuntimeException;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class ParameterResolver
{
    use StyleAwareTrait;

    /**
     * @var DependencyInjectionParameterResolver
     */
    private $resolver;

    /**
     * @param StyleInterface                       $style
     * @param DependencyInjectionParameterResolver $dependencyInjectionParameterResolver
     */
    public function __construct(StyleInterface $style, DependencyInjectionParameterResolver $dependencyInjectionParameterResolver)
    {
        $this->io = $style;
        $this->resolver = $dependencyInjectionParameterResolver;
    }

    /**
     * @param string $name
     * @param bool   $required
     *
     * @return mixed
     */
    public function resolve(string $name, bool $required = true)
    {
        $exceptions = [];

        try {
            return $this->resolveArgument($name, $required);
        } catch (RuntimeException $exception) {
            $exceptions[] = $exception;
        }

        try {
            return $this->resolveOption($name, $required);
        } catch (RuntimeException $exception) {
            $exceptions[] = $exception;
        }

        if ($required) {
            throw $this->createMultiResolveErrorException(...$exceptions);
        }

        return null;
    }

    /**
     * @param string $name
     * @param bool   $required
     *
     * @return mixed
     */
    public function resolveArgument(string $name, bool $required = true)
    {
        return $this->doResolution('argument', $name, $required);
    }

    /**
     * @param string $name
     * @param bool   $required
     *
     * @return mixed
     */
    public function resolveOption($name, $required = true)
    {
        return $this->doResolution('option', $name, $required);
    }

    /**
     * @param string $type
     * @param string $name
     * @param bool   $required
     *
     * @throws RuntimeException
     *
     * @return mixed|null
     */
    private function doResolution($type, $name, $required)
    {
        try {
            return is_array($value = $this->getValue($type, $name)) ? $this->resolveArray($value) : $this->resolveScalar($value);
        } catch (InvalidArgumentException $exception) {
            if ($required) {
                throw $this->createSingleResolveErrorException($type, $name, $exception);
            }
        }

        return null;
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    private function resolveArray(array $values)
    {
        return array_map(function ($v) {
            return $this->resolveScalar($v);
        }, $values);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function resolveScalar($value)
    {
        return $this->resolver->resolveValue($value);
    }

    /**
     * @param string $type
     * @param string $name
     *
     * @return mixed
     */
    private function getValue($type, $name)
    {
        return $type === 'option' ? $this->io->getInput()->getOption($name) : $this->io->getInput()->getArgument($name);
    }

    /**
     * @param RuntimeException[] ...$exceptions
     *
     * @return RuntimeException
     */
    private function createMultiResolveErrorException(RuntimeException ...$exceptions): RuntimeException
    {
        $message = implode(' / ', array_map(function (RuntimeException $exception) {
            return $exception->getMessage();
        }, $exceptions));

        return new RuntimeException('Encountered %d exceptions: %s', count($exceptions), $message, array_pop($exceptions));
    }

    /**
     * @param string     $type
     * @param string     $name
     * @param \Exception $exception
     *
     * @return RuntimeException
     */
    private function createSingleResolveErrorException($type, $name, \Exception $exception): RuntimeException
    {
        if ($message = $this->thrownInternalMessage($type, $name) !== $exception->getMessage()) {
            $message = $this->thrownExternalMessage($type, $name, $exception);
        }

        return new RuntimeException($message, $exception);
    }

    /**
     * @param string $type
     * @param string $name
     *
     * @return string[]
     */
    private function thrownInternalMessage($type, $name)
    {
        return sprintf('Missing required %s "%s"', $type, $name);
    }

    /**
     * @param string     $type
     * @param string     $name
     * @param \Exception $exception
     *
     * @return string[]
     */
    private function thrownExternalMessage($type, $name, \Exception $exception)
    {
        return sprintf('%s (Invalid value for %s "%s")', $exception->getMessage(), $type, $name);
    }
}
