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

use Rf\AppBundle\Component\Search\Indexing\Model\IndexableEntity;
use SR\Doctrine\ORM\Mapping\Entity;

interface EntityProviderInterface extends \Countable
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param bool $qualified
     *
     * @return string
     */
    public function getClass(bool $qualified = true): string;

    /**
     * @return int
     */
    public function count(): int;

    /**
     * @return string[]|int[]
     */
    public function getIdentities(): array;

    /**
     * @return Entity[]
     */
    public function getEntities(): array;

    /**
     * @return IndexableEntity[]
     */
    public function getIndexableModels(): array;

    /**
     * @return \Generator|string[]
     */
    public function forEachIdentities(): \Generator;

    /**
     * @return \Generator|Entity[]
     */
    public function forEachEntities(): \Generator;

    /**
     * @return \Generator|IndexableEntity[]
     */
    public function forEachIndexableModels(): \Generator;
}
