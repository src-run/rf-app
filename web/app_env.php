<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

$productionIndex = 'APP_ENVIRONMENT_PROD';

if (false !== getenv($productionIndex)) {
    return (bool) getenv($productionIndex);
}

if (isset($_SERVER['HTTP_'.$productionIndex])) {
    return (bool) $_SERVER['HTTP_'.$productionIndex];
}

return true;
