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

final class NamedClosureInstruction extends AbstractInstruction
{
    /**
     * @var \Closure
     */
    private $closure;

    /**
     * @param string   $name
     * @param \Closure $closure
     */
    public function __construct(string $name, \Closure $closure)
    {
        parent::__construct($name);

        $this->closure = $closure;
    }

    /**
     * @return InstructionInterface
     */
    public function run(): InstructionInterface
    {
        $this->setResult($this->getClosure()());

        return $this;
    }

    /**
     * @return \Closure
     */
    private function getClosure(): \Closure
    {
        return $this->closure;
    }
}
