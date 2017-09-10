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

use Rf\AppBundle\Component\Console\Runner\AbstractRunner;

abstract class AbstractFileRunner extends AbstractRunner
{
    /**
     * @var \SplFileInfo[]
     */
    protected $filesSkipped = [];

    /**
     * @var \SplFileInfo[]
     */
    protected $filesActedOn = [];

    /**
     * @var string
     */
    protected $repositoryPath;

    /**
     * @var string[]
     */
    protected $ignoredFiles;

    public function __construct()
    {
        parent::__construct(null);
    }

    /**
     * @param string $repositoryPath
     *
     * @return self
     */
    public function setRepositoryPath(string $repositoryPath): self
    {
        $this->repositoryPath = $repositoryPath;

        return $this;
    }

    /**
     * @param array $ignoredFiles
     *
     * @return self
     */
    public function setIgnoredFiles(array $ignoredFiles): self
    {
        $this->ignoredFiles = $ignoredFiles;

        return $this;
    }

    /**
     * @param string $action
     */
    protected function writeActionSummary(string $action): void
    {
        $this->io->info(sprintf('%s %d files (and skipped %d files).', ucfirst($action), count($this->filesActedOn), count($this->filesSkipped)));
    }

    /**
     * @param string        $path
     * @param \Closure|null $filter
     *
     * @return \Generator|\SplFileInfo[]
     */
    protected function yieldFilesIn(string $path, \Closure $filter = null): \Generator
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::CATCH_GET_CHILD) as $file) {
            if (($filter instanceof \Closure && false === $filter($file)) || $this->isPathNav($file) || $file->isDir()) {
                continue;
            }

            yield $file;
        }
    }

    /**
     * @param string        $path
     * @param \Closure|null $filter
     *
     * @return \Generator|\SplFileInfo[]
     */
    protected function yieldDotFilesIn(string $path, \Closure $filter = null): \Generator
    {
        foreach ($this->yieldFilesIn($path, $filter) as $file) {
            if (!$this->isFileDot($file)) {
                continue;
            }

            yield $file;
        }
    }

    /**
     * @param \SplFileInfo $file
     *
     * @return bool
     */
    private function isPathNav(\SplFileInfo $file): bool
    {
        return $file->getBasename() === '.' || $file->getBasename() === '..';
    }

    /**
     * @param \SplFileInfo $file
     *
     * @return bool
     */
    private function isFileDot(\SplFileInfo $file): bool
    {
        return 0 === strpos($file->getBasename(), '.');
    }

    /**
     * @param \SplFileInfo $file
     *
     * @return string|null
     */
    protected function handleSkippedFile(\SplFileInfo $file): ?string
    {
        if (in_array($file->getBasename(), $this->ignoredFiles)) {
            return 'user-ignored';
        }

        if (false !== $file->getRealPath() && 0 !== strpos($file->getRealPath(), $this->repositoryPath)) {
            return 'external-link';
        }

        if (property_exists($this, 'noOverwrite') && $this->noOverwrite && file_exists($file)) {
            return 'no-overwrite';
        }

        return null;
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
