<?php

/*
 * This file is part of the `src-run/srw-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Environment;

use Symfony\Component\VarDumper\VarDumper;

class SymfonyEnvironment implements EnvironmentInterface
{
    /**
     * @var string
     */
    private $environment;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @param string $environment
     * @param bool   $debug
     */
    public function __construct(string $environment, bool $debug)
    {
        $this->environment = $environment;
        $this->debug = $debug;
    }

    /**
     * @return bool
     */
    public function isProduction(): bool
    {
        return $this->isEnvironment('prod');
    }

    /**
     * @return bool
     */
    public function isDevelopment(): bool
    {
        return $this->isEnvironment('dev');
    }

    /**
     * @param string $environment
     *
     * @return bool
     */
    public function isEnvironment(string $environment): bool
    {
        return $this->environment === $environment;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }
}
