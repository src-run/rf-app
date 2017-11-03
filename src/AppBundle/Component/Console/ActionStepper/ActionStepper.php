<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Console\ActionStepper;

use Rf\AppBundle\Component\Console\ActionStepper\Instruction\AbstractInstruction;
use Rf\AppBundle\Component\Console\ActionStepper\Instruction\MethodReflectionInstruction;
use Rf\AppBundle\Component\Console\ActionStepper\Instruction\NamedClosureInstruction;
use SR\Console\Output\Style\StyleAwareTrait;

class ActionStepper
{
    use StyleAwareTrait;

    /**
     * @var AbstractInstruction[]
     */
    private $instructions;

    /**
     * @var int
     */
    private $result = 0;

    /**
     * @param AbstractInstruction[] ...$instructions
     */
    public function __construct(AbstractInstruction ...$instructions)
    {
        $this->instructions = $instructions;
    }

    /**
     * @param string   $name
     * @param \Closure $closure
     *
     * @return AbstractInstruction
     */
    public function addActionClosure(string $name, \Closure $closure): AbstractInstruction
    {
        $this->instructions[] = $instruction = new NamedClosureInstruction($name, $closure);

        return $instruction;
    }

    /**
     * @param string          $method
     * @param \Closure|object $context
     * @param array           ...$arguments
     *
     * @return AbstractInstruction
     */
    public function addActionMethod(string $method, $context, ...$arguments): AbstractInstruction
    {
        $this->instructions[] = $instruction = new MethodReflectionInstruction($method, $context, ...$arguments);

        return $instruction;
    }

    /**
     * @return int
     */
    public function run(): int
    {
        foreach ($this->instructions as $instruction) {
            if (false === $this->doInstructionRun($instruction)) {
                return $this->doInstructionErr($instruction)->getResult();
            }
        }

        return 0;
    }

    /**
     * @return int
     */
    public function getResult(): int
    {
        return $this->result;
    }

    /**
     * @param int $result
     *
     * @return self
     */
    private function setResult(int $result): self
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @param AbstractInstruction $instruction
     *
     * @return bool
     */
    private function doInstructionRun(AbstractInstruction $instruction): bool
    {
        if (!$instruction->isConditionalMet()) {
            if ($this->io->isDebug()) {
                $this->io->comment(sprintf('Skipping instruction definition: %s', $instruction->getName()));
            }

            return true;
        }

        $this->io->title($instruction->getTitleString());

        return $instruction->run()->isSuccess() || $instruction->isErrorInconsequential();
    }

    /**
     * @param AbstractInstruction $instruction
     *
     * @return self
     */
    private function doInstructionErr(AbstractInstruction $instruction): self
    {
        $this->io->critical($instruction->getErrorString());
        $this->setResult($instruction->getResult());

        return $this;
    }
}
