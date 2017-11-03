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

interface InstructionInterface
{
    /**
     * @return self
     */
    public function run(): self;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param int $result
     *
     * @return self
     */
    public function setResult(int $result): self;

    /**
     * @return bool
     */
    public function hasResult(): bool;

    /**
     * @return int
     */
    public function getResult(): int;

    /**
     * @return bool
     */
    public function isSuccess(): bool;

    /**
     * @param \Closure $conditional
     *
     * @return self
     */
    public function setConditional(\Closure $conditional): self;

    /**
     * @return bool
     */
    public function isConditionalMet(): bool;

    /**
     * @param string $sectionMessage
     *
     * @return self
     */
    public function setTitleString(string $sectionMessage): self;

    /**
     * @return string
     */
    public function getTitleString(): string;

    /**
     * @param string $failureMessage
     *
     * @return InstructionInterface
     */
    public function setErrorString(string $failureMessage): self;

    /**
     * @return string
     */
    public function getErrorString(): string;

    /**
     * @param bool $continue
     *
     * @return InstructionInterface
     */
    public function setErrorInconsequential(bool $continue): self;

    /**
     * @return bool
     */
    public function isErrorInconsequential(): bool;
}
