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

abstract class AbstractInstruction implements InstructionInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $result;

    /**
     * @var \Closure|null
     */
    private $conditional;

    /**
     * @var string|null
     */
    private $titleString;

    /**
     * @var string|null
     */
    private $errorString;

    /**
     * @var bool
     */
    private $errorInconsequential = false;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param int $result
     *
     * @return InstructionInterface
     */
    public function setResult(int $result): InstructionInterface
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasResult(): bool
    {
        return null !== $this->result;
    }

    /**
     * @return int
     */
    public function getResult(): int
    {
        return $this->result;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return 0 === $this->result;
    }

    /**
     * @param \Closure $conditional
     *
     * @return InstructionInterface
     */
    public function setConditional(\Closure $conditional): InstructionInterface
    {
        $this->conditional = $conditional;

        return $this;
    }

    /**
     * @return bool
     */
    public function isConditionalMet(): bool
    {
        if ($this->conditional instanceof \Closure) {
            $conditional = $this->conditional;

            return $conditional();
        }

        return true;
    }

    /**
     * @param string $titleString
     *
     * @return InstructionInterface
     */
    public function setTitleString(string $titleString): InstructionInterface
    {
        $this->titleString = $titleString;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitleString(): string
    {
        return $this->titleString ?? sprintf('Running "%s" action instruction...', $this->getName());
    }

    /**
     * @param string $errorString
     *
     * @return InstructionInterface
     */
    public function setErrorString(string $errorString): InstructionInterface
    {
        $this->errorString = $errorString;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorString(): string
    {
        return $this->errorString ?? sprintf('Instruction "%s" failed with result code %d.', $this->getName(), $this->getResult());
    }

    /**
     * @param bool $continue
     *
     * @return InstructionInterface
     */
    public function setErrorInconsequential(bool $continue): InstructionInterface
    {
        $this->errorInconsequential = $continue;

        return $this;
    }

    /**
     * @return bool
     */
    public function isErrorInconsequential(): bool
    {
        return $this->errorInconsequential;
    }
}
