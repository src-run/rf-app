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

abstract class AbstractFileRunner
{
    /**
     * @var StyleInterface
     */
    protected $io;

    /**
     * @var string
     */
    protected $repositoryPath;

    /**
     * @var string[]
     */
    protected $ignoredFiles;

    /**
     * @param StyleInterface $io
     * @param string         $repositoryPath
     * @param string[]       $ignoredFiles
     */
    public function __construct(StyleInterface $io, string $repositoryPath, array $ignoredFiles)
    {
        $this->io = $io;
        $this->repositoryPath = $repositoryPath;
        $this->ignoredFiles = $ignoredFiles;
    }

    /**
     * @param string $path
     *
     * @return string[]
     */
    protected function findFilesInPath(string $path, bool $dot = true): array
    {
        return $this->buildScannedFilePaths(array_filter(scandir($path), function (string $file) use ($dot) {
            return $file !== '.'
                && $file !== '..'
                && (0 === strpos($file, '.')) === $dot
                && !is_dir($this->repositoryPath.DIRECTORY_SEPARATOR.$file);
        }));
    }

    /**
     * @param array $files
     *
     * @return array
     */
    private function buildScannedFilePaths(array $files): array
    {
        return array_values(array_map(function (string $file) {
            return $this->buildFilePath($file);
        }, $files));
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function buildFilePath(string $file): string
    {
        return $this->repositoryPath.DIRECTORY_SEPARATOR.$file;
    }

    /**
     * @param array  $actionOneList
     * @param string $actionOne
     * @param array  $actionTwoList
     * @param string $actionTwo
     */
    protected function outputResults(array $actionOneList, string $actionOne, array $actionTwoList, string $actionTwo)
    {
        if (count($actionOneList) > 0) {
            $this->io->info(sprintf('%s "%d" existing dot files', ucfirst($actionOne), count($actionOneList)));

            if ($this->io->isVerbose()) {
                $this->io->listing($actionOneList);
            }
        }

        $this->io->info(sprintf('%s "%d" existing dot files', ucfirst($actionTwo), count($actionTwoList)));

        if (count($actionTwoList) > 0 && $this->io->isVerbose()) {
            $this->io->listing($actionTwoList);
        }
    }
}
