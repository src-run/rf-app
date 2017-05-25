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

use Rf\AppBundle\Component\DependencyInjection\ParameterResolver;
use SR\Console\Output\Style\StyleAwareTrait;
use SR\Console\Output\Style\StyleInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\VarDumper\VarDumper;

class InputParamResolver
{
    use StyleAwareTrait;

    /**
     * @var OutputErrorHandler
     */
    private $outputErrorHandler;

    /**
     * @var ParameterResolver
     */
    private $parameterResolver;

    /**
     * @param StyleInterface     $style
     * @param OutputErrorHandler $outputErrorHandler
     * @param ParameterResolver  $parameterResolver
     */
    public function __construct(StyleInterface $style, OutputErrorHandler $outputErrorHandler, ParameterResolver $parameterResolver)
    {
        $this->io = $style;
        $this->outputErrorHandler = $outputErrorHandler;
        $this->parameterResolver = $parameterResolver;
    }

    /**
     * @param string $name
     * @param bool   $required
     *
     * @return mixed
     */
    public function resolveArgument($name, $required = true)
    {
        return $this->resolve('argument', $name, $required);
    }

    /**
     * @param string $name
     * @param bool   $required
     *
     * @return mixed
     */
    public function resolveOption($name, $required = true)
    {
        return $this->resolve('option', $name, $required);
    }

    /**
     * @param string $type
     * @param string $name
     * @param bool   $required
     *
     * @return mixed|null
     */
    private function resolve($type, $name, $required)
    {
        try {
            return $this->parameterResolver->resolveValue($this->getValue($type, $name));
        } catch (InvalidArgumentException $exception) {
            $this->handleThrownException($type, $name, $required, $exception);

            return null;
        }
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
     * @param string     $type
     * @param string     $name
     * @param bool       $required
     * @param \Exception $exception
     */
    private function handleThrownException($type, $name, $required, \Exception $exception)
    {
        if ($message = $this->thrownInternalMessage($type, $name) !== $exception->getMessage()) {
            $message = $this->thrownExternalMessage($type, $name, $exception);
        }

        $this->handleError($message, $required);
    }

    /**
     * @param string $type
     * @param string $name
     *
     * @return string[]
     */
    private function thrownInternalMessage($type, $name)
    {
        return [ sprintf('Missing required %s "%s"', $type, $name) ];
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
        return [ sprintf('%s (Invalid value for %s "%s")', $exception->getMessage(), $type, $name) ];
    }

    /**
     * @param string[] $message
     * @param bool     $fatal
     */
    private function handleError(array $message, bool $fatal = false): void
    {
        foreach ((array) array_splice($message, 0, count($message) - 1) as $line) {
            $this->outputErrorHandler->raiseWarning($line);
        }

        if (true === $fatal) {
            $this->outputErrorHandler->raiseCritical($message[count($message) - 1]);

            return;
        }

        $this->outputErrorHandler->raiseError($message[count($message) - 1]);
    }
}
