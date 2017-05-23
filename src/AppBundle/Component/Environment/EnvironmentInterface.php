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

interface EnvironmentInterface
{
    /**
     * @return bool
     */
    public function isProduction(): bool;

    /**
     * @return bool
     */
    public function isDevelopment(): bool;

    /**
     * @param string $environment
     *
     * @return bool
     */
    public function isEnvironment(string $environment): bool;

    /**
     * @return bool
     */
    public function isDebug(): bool;
}
