<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Console\IO;

use SR\Console\Output\Helper\DecorationHelper;
use SR\Console\Output\Style\StyleInterface;

final class ActionIO
{
    /**
     * @var mixed[]
     */
    private $io;

    /**
     * @param StyleInterface $io
     */
    public function __construct(StyleInterface $io)
    {
        $this->io = $io;
    }

    /**
     * @param string $action
     * @param bool   $initNewline
     *
     * @return self
     */
    public function action(string $action, bool $initNewline = false): self
    {
        if ($this->io->isVerbose()) {
            if ($initNewline) {
                $this->io->newline();
            }

            $this->io->write(sprintf('   - %s ... ', $action));
        }

        return $this;
    }

    /**
     * @param string $action
     * @param bool   $initNewline
     * @param bool   $endingNewline
     *
     * @return self
     */
    public function actionSingle(string $action, bool $initNewline = true, bool $endingNewline = true): self
    {
        if ($this->io->isVerbose()) {
            if ($initNewline) {
                $this->io->newline();
            }

            $this->io->write(sprintf('   - %s', $action))->newline();

            if ($endingNewline) {
                $this->io->newline();
            }
        }

        return $this;
    }

    /**
     * @param string $status
     *
     * @return self
     */
    public function status(string $status): self
    {
        if ($this->io->isVerbose()) {
            $this->io->write(sprintf('%s ', $status));
        }

        return $this;
    }

    /**
     * @param int    $count
     * @param string $what
     *
     * @return self
     */
    public function enumeration(int $count, string $what = 'items'): self
    {
        return $this->status(sprintf('(%\'06d %s)', $count, $count === 1 ? substr($what, 0, -1) : $what));
    }

    /**
     * @param int $position
     * @param int $size
     */
    public function progress(int $position, int $size): void
    {
        static $displayed = [];

        if (!$this->io->isVeryVerbose()) {
            return;
        }

        $percent = 100 * $position / $size;
        $floored = floor($percent);

        if ($position === 0) {
            $this->io->write('[');
            $displayed = [];
        }

        if ($position === ($size - 1)) {
            $this->io->write('100%] ');
        } elseif (0 === $floored % 10 && !in_array($floored, $displayed)) {
            $this->io->write(sprintf('%d%%', $floored));
            $displayed[] = $floored;
        } elseif (0 === $floored % 2 && !in_array($floored, $displayed)) {
            $this->io->write('.');
            $displayed[] = $floored;
        }
    }

    /**
     * @param bool $newline
     *
     * @return self
     */
    public function okay(bool $newline = false): self
    {
        return $this->done('OK', $newline);
    }

    /**
     * @param bool $newline
     *
     * @return self
     */
    public function remove(bool $newline = false): self
    {
        return $this->done('RM', $newline, false);
    }

    /**
     * @param string $result
     * @param bool   $newline
     * @param bool   $success
     *
     * @return self
     */
    public function done(string $result, bool $newline = false, bool $success = true): self
    {
        $decorator = $success ? new DecorationHelper('black', 'green') : new DecorationHelper('white', 'red');

        if ($this->io->isVerbose()) {
            $this->io->writeln([sprintf('%s', $decorator->decorate(sprintf(' %s ', strtoupper($result))))]);

            if ($newline) {
                $this->io->newline();
            }
        }

        return $this;
    }
}
