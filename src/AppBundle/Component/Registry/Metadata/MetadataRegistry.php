<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Registry\Metadata;

class MetadataRegistry implements \IteratorAggregate, \Countable
{
    /**
     * @var MetaEntry[]
     */
    private $metaEntries;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->metaEntries = array_map(function (array $attributes) {
            return new MetaEntry($attributes);
        }, $config);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->getMetaEntries());
    }

    /**
     * @return \ArrayIterator|MetaEntry[]
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->getMetaEntries());
    }

    /**
     * @return MetaEntry[]
     */
    public function getMetaEntries(): array
    {
        return $this->metaEntries;
    }
}
