<?php

/*
 * This file is part of the `src-run/srw-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use Composer\Autoload\ClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

/** @var ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';

if (false === $production = (bool) getenv('APP_ENVIRONMENT_PROD')) {
    Debug::enable();
}

/** @var AppKernel $kernel */
$kernel = new AppKernel($production ? 'prod' : 'dev', !$production);

$response = $kernel
    ->handle($request = Request::createFromGlobals())
    ->send();

$kernel->terminate($request, $response);
