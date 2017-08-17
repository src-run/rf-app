<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Console\Runner;

use SR\Console\Output\Style\StyleInterface;

abstract class AbstractRunner
{
    /**
     * @var StyleInterface
     */
    protected $io;

    /**
     * @var int
     */
    protected $result = 0;

    /**
     * @param StyleInterface|null $io
     */
    public function __construct(StyleInterface $io = null)
    {
        $this->io = $io;
    }

    /**
     * @param StyleInterface $style
     *
     * @return self
     */
    public function setStyle(StyleInterface $style): self
    {
        $this->io = $style;

        return $this;
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
    protected function setResult(int $result): self
    {
        $this->result = $result;

        return $this;
    }
}
