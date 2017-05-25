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

class EnvRemoveFileRunner extends AbstractFileRunner
{
    /**
     * @return int
     */
    public function run()
    {
        $this->io->section('Remove existing dot files runner');

        $filesRemoved = $filesSkipped = [];

        foreach ($this->findFilesInPath($this->repositoryPath) as $file) {
            if (in_array(basename($file), $this->ignoredFiles)) {
                $filesSkipped[] = $file;
                continue;
            }

            if (false === $real = realpath($file)) {
                $this->io->warning(sprintf('Skipping removal of "%s" as an undefined error was encountered during resolution.', $file));
                continue;
            }

            if (0 !== strpos($real, $this->repositoryPath)) {
                $this->io->warning(sprintf('Skipping removal of "%s" as it is outside the repository root "%s".', $real, $this->repositoryPath));
                continue;
            }

            $filesRemoved[] = $this->removeFile($real);
        }

        $this->outputResults($filesSkipped, 'skipped', $filesRemoved, 'removed');

        return 0;
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function removeFile(string $file): string
    {
        if (!file_exists($file) || !is_writable($file)) {
            $this->io->warning(sprintf('Unable to remove "%s" due to write permissions.', $file));
        }

        @unlink($file);

        return $file;
    }
}
