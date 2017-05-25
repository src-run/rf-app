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

use SR\Console\Output\Style\StyleAwareTrait;
use SR\Console\Output\Style\StyleInterface;

class OutputErrorHandler
{
    use StyleAwareTrait;

    /**
     * @param StyleInterface $style
     */
    public function __construct(StyleInterface $style)
    {
        $this->setStyle($style);
    }

    /**
     * @param string $message
     * @param mixed  ...$replacements
     */
    public function raiseWarning($message, ...$replacements)
    {
        $this->io->warning($this->buildMessage($message, ...$replacements));
    }

    /**
     * @param string $message
     * @param mixed  ...$replacements
     */
    public function raiseError($message, ...$replacements)
    {
        $this->io->error($this->buildMessage($message, ...$replacements));
    }

    /**
     * @param string $message
     * @param mixed  ...$replacements
     *
     * @throws \RuntimeException
     */
    public function raiseCritical($message, ...$replacements)
    {
        $this->io->critical($this->buildMessage($message, ...$replacements));
        $this->outputHelp(true);

        throw new \RuntimeException('Fatal error encountered: '.$message);
    }

    /**
     * @param string $message
     * @param mixed  ...$replacements
     */
    public function raiseCriticalAndExitImmediately($message, ...$replacements)
    {
        $this->io->critical($this->buildMessage($message, ...$replacements));
        $this->outputHelp(true);

        exit(-1);
    }

    private function outputHelp($fatal = true)
    {
        $extra = $fatal ? 'Exiting due to prior fatal error...' : 'Attempting to continue despite prior error...';
        $this->io->comment(sprintf('Use "--help" to display command usage details. %s', $extra));
    }

    /**
     * @param string  $message
     * @param mixed[] ...$replacements
     *
     * @return string
     */
    private function buildMessage($message, ...$replacements)
    {
        if (count($replacements) > 0) {
            return sprintf($message, ...$replacements);
        }

        return $message;
    }
}
