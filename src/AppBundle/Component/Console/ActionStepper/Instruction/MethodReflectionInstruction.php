<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Console\ActionStepper\Instruction;

final class MethodReflectionInstruction extends AbstractInstruction
{
    /**
     * @var object
     */
    private $context;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @param string $method
     * @param object $context
     * @param array  ...$arguments
     */
    public function __construct(string $method, $context, ...$arguments)
    {
        parent::__construct($method);

        $this->context = $context;
        $this->arguments = $arguments;
    }

    /**
     * @return InstructionInterface
     */
    public function run(): InstructionInterface
    {
        $this->setResult($this->getMethod()->invokeArgs($this->context, $this->arguments));

        return $this;
    }

    /**
     * @return \ReflectionMethod
     */
    private function getMethod(): \ReflectionMethod
    {
        $method = (new \ReflectionObject($this->context))->getMethod($this->getName());

        if ($method->isPrivate() || $method->isProtected()) {
            $method->setAccessible(true);
        }

        return $method;
    }
}
