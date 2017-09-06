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

class EnvInstallFileRunner extends AbstractFileRunner
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
     * @param StyleInterface $io
     * @param string         $environment
     * @param string         $mapsRepoPath
     * @param string         $configurationPath
     * @param string[]       $ignoredFiles
     */
    public function __construct(StyleInterface $io, string $environment, string $mapsRepoPath, string $configurationPath, array $ignoredFiles)
    {
        parent::__construct($io, $mapsRepoPath, $ignoredFiles);

        $this->environment = $environment;
        $this->configurationPath = $configurationPath;
    }

    public function run()
    {
        $this->io->section(sprintf('Install existing dot files runner (%s env)', $this->environment));

        $filesInstall = $filesSkipped = [];
        foreach ($this->findFilesInPath($this->getEnvironmentPath(), false) as $file) {
            if (in_array(basename($file), $this->ignoredFiles)) {
                $filesSkipped[] = $file;
                continue;
            }

            if (false === $real = realpath($file)) {
                $this->io->warning(sprintf('Skipping installation of "%s" as an undefined error was encountered during resolution.', $file));
                continue;
            }

            if (0 !== strpos($real, $this->repositoryPath)) {
                $this->io->warning(sprintf('Skipping installation of "%s" as it is outside the repository root "%s".', $real, $this->repositoryPath));
                continue;
            }

            $filesInstall[] = $this->installFile($real);
        }

        $this->outputResults($filesSkipped, 'skipped', $filesInstall, 'installed');
        $this->setResult($filesInstall > 0 ? 0 : 1);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function installFile(string $file): string
    {
        $copyTo = $this->repositoryPath.DIRECTORY_SEPARATOR.sprintf('.%s', basename($file));

        if (false === @copy($file, $copyTo)) {
            $this->io->warning(sprintf('Unable to install "%s" to "%s" due to write permissions.', $file, $copyTo));
        }

        return sprintf('%s -> %s', $file, $copyTo);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function buildFilePath(string $file) : string
    {
        return $this->getEnvironmentPath().DIRECTORY_SEPARATOR.$file;
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
