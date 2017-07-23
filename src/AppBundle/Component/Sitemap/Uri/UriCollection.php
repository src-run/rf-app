<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Sitemap\Uri;

class UriCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var UriDefinition[]
     */
    private $definitions;

    /**
     * @param UriDefinition[] ...$definitions
     */
    public function __construct(UriDefinition ...$definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * @param UriDefinition $definition
     * @param bool          $allowDuplicates
     *
     * @return UriCollection
     */
    public function add(UriDefinition $definition, bool $allowDuplicates = false): self
    {
        if ($allowDuplicates || !$this->has($definition)) {
            $this->definitions[] = $definition;
        }

        return $this;
    }

    /**
     * @param UriDefinition $definition
     *
     * @return UriCollection
     */
    public function del(UriDefinition $definition): self
    {
        if (false !== $key = array_search($definition, $this->definitions)) {
            unset($this->definitions[$key]);
        }

        return $this;
    }

    /**
     * @param UriDefinition $definition
     *
     * @return bool
     */
    public function has(UriDefinition $definition): bool
    {
        return in_array($definition, $this->definitions);
    }

    /**
     * @param UriCollection $collection
     *
     * @return UriCollection
     */
    public function merge(UriCollection $collection): self
    {
        foreach ($collection as $definition) {
            $this->add($definition);
        }

        return $this;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->definitions);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->definitions);
    }

    /**
     * @param \Closure $closure
     *
     * @return self
     */
    public function sort(\Closure $closure): self
    {
        usort($this->definitions, $closure);

        return $this;
    }
}
