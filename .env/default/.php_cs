<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

require __DIR__.'/.bldr/_helpers/php-cs-fixer/config.php';

return (new SR\PhpCsFixer\Config(['location' => __DIR__, 'header' => true]))->create();
