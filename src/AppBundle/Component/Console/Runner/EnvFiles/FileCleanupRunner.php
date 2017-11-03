<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Console\Runner\EnvFiles;

use SR\Console\Output\Style\StyleInterface;

class FileCleanupRunner extends AbstractFileRunner
{
    public function run(): void
    {
        $this->io->section('Cleanup');

        if (false === $this->cleanupDotFiles()) {
            $this->setResult(255);
        }

        $this->writeActionSummary('un-linked');
    }

    /**
     * @return bool
     */
    private function cleanupDotFiles(): bool
    {
        foreach ($this->yieldDotFilesIn($this->repositoryPath) as $file) {
            $this->handleFile($file);
        }

        return true;
    }

    /**
     * @param \SplFileInfo $file
     */
    private function handleFile(\SplFileInfo $file): void
    {
        $this->io
            ->environment(StyleInterface::VERBOSITY_VERY_VERBOSE)
            ->action(sprintf('Un-linking "%s"', $file->getPathname()));

        if (null !== $action = $this->handleSkippedFile($file)) {
            $this->filesSkipped[] = $file;
            $this->io
                ->environment(StyleInterface::VERBOSITY_VERY_VERBOSE)
                ->write(sprintf('(%s) ', $action))
                ->actionStop('skipped');
        } elseif ($this->doRemoveFile($file)) {
            $this->filesActedOn[] = $file;
            $this->io
                ->environment(StyleInterface::VERBOSITY_VERY_VERBOSE)
                ->actionOkay();
        } else {
            $this->filesSkipped[] = $file;
            $this->io
                ->environment(StyleInterface::VERBOSITY_VERY_VERBOSE)
                ->actionFail();
        }
    }

    /**
     * @param \SplFileInfo $file
     *
     * @return bool
     */
    private function doRemoveFile(\SplFileInfo $file): bool
    {
        if ($this->isDryRun()) {
            return true;
        }

        if (!file_exists($file->getPathname()) || !is_writable($file->getPathname()) || !@unlink($file)) {
            $this->io->error(sprintf('Unable to remove "%s" file: it does not exist or is not writeable.', $file->getPathname()));

            return false;
        }

        return true;
    }
}
