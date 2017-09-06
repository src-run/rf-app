<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Search\Indexing;

use Rf\AppBundle\Component\Search\Indexing\Model\IndexableEntityModel;

interface EntityProviderInterface
{
    /**
     * @return \Generator|IndexableEntityModel[]
     */
    public function getModels(): \Generator;
}
