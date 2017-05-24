<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

if (!$loader = @include __DIR__.'/../app/autoload.php') {
    echo <<<'EOM'
You must set up the project dependencies by running the following commands:

curl -s http://getcomposer.org/installer | php
php composer.phar install

EOM;
    exit(1);
}
