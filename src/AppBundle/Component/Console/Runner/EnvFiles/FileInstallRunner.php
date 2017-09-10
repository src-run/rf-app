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

class FileInstallRunner extends AbstractFileRunner
{
    /**
     * @var string
     */
    private $environment;

    /**
     * @var string
     */
    private $configurationPath;

    /**
     * @var bool
     */
    protected $noOverwrite;

    /**
     * @param string $environment
     *
     * @return self
     */
    public function setEnvironment(string $environment): self
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * @param string $configurationPath
     *
     * @return self
     */
    public function setConfigurationPath(string $configurationPath): self
    {
        $this->configurationPath = $configurationPath;

        return $this;
    }

    /**
     * @param bool $noOverwrite
     *
     * @return self
     */
    public function setNoOverwrite(bool $noOverwrite): self
    {
        $this->noOverwrite = $noOverwrite;

        return $this;
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $this->io->section(sprintf('Install (%s)', $this->environment));

        if (false === $this->installDotFiles()) {
            $this->setResult(255);
        }

        $this->writeActionSummary('installed');
    }

    /**
     * @return bool
     */
    private function installDotFiles(): bool
    {
        foreach ($this->yieldFilesIn($this->getEnvironmentPath()) as $file) {
            $this->handleFile($file);
        }

        return true;
    }

    private function handleFile(\SplFileInfo $file): void
    {
        $this->io
            ->environment(StyleInterface::VERBOSITY_VERBOSE)
            ->action(sprintf('Installing "%s"', $file->getPathname()));

        if (null !== $action = $this->handleSkippedFile($this->getOutputFile($file))) {
            $this->filesSkipped[] = $file;
            $this->io
                ->environment(StyleInterface::VERBOSITY_VERBOSE)
                ->write(sprintf('(%s) ', $action))
                ->actionStop('skipped');
        } elseif ($this->doInstallFile($file)) {
            $this->filesActedOn[] = $file;
            $this->io
                ->environment(StyleInterface::VERBOSITY_VERBOSE)
                ->actionOkay();
        } else {
            $this->filesSkipped[] = $file;
            $this->io
                ->environment(StyleInterface::VERBOSITY_VERBOSE)
                ->actionFail();
        }
    }

    /**
     * @param \SplFileInfo $file
     *
     * @return bool
     */
    private function doInstallFile(\SplFileInfo $file): bool
    {
        if ((file_exists($file->getPathname()) && !is_readable($file->getPathname())) || !is_writable($file->getPath()) || !@copy($file, $this->getOutputFile($file))) {
            $this->io->error(sprintf('Unable to install "%s" file: the path is not writable or the copy otherwise failed.', $file->getPathname()));

            return false;
        }

        return true;
    }

    /**
     * @param \SplFileInfo $file
     *
     * @return \SplFileInfo
     */
    private function getOutputFile(\SplFileInfo $file): \SplFileInfo
    {
        return new \SplFileInfo($this->repositoryPath.DIRECTORY_SEPARATOR.$file->getBasename());
    }

    /**
     * @return string
     */
    private function getEnvironmentPath(): string
    {
        if (false === $path = realpath($this->configurationPath.DIRECTORY_SEPARATOR.$this->environment)) {
            $this->io->critical('Environment %s does not exist in %s.', $this->environment, $this->configurationPath);
        }

        return $path;
    }
}
