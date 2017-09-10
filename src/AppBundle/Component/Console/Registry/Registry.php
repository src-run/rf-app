<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Console\Registry;

use SR\Exception\Logic\InvalidArgumentException;

class Registry
{
    /**
     * @var mixed[]
     */
    private $elements;

    /**
     * @var bool
     */
    private $strict;

    /**
     * @param array $elements
     * @param bool  $strict
     */
    public function __construct(array $elements = [], bool $strict = false)
    {
        $this->elements = $elements;
        $this->strict = $strict;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return self
     */
    public function set(string $name, $value): self
    {
        if ($this->isStrict() && $this->has($name)) {
            throw new InvalidArgumentException('Cannot set element %s as it already exists.', $name);
        }

        $this->elements[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function get(string $name)
    {
        if ($this->isStrict() && false === $this->has($name)) {
            throw new InvalidArgumentException('Requested element name %s does not exist.', $name);
        }

        return $this->has($name) ? $this->elements[$name] : null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->elements[$name]);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function hasElement($value): bool
    {
        return in_array($value, $this->elements);
    }

    /**
     * @param $value
     *
     * @return null|string
     */
    public function getElementKey($value): ?string
    {
        if (false !== $name = array_search($value, $this->elements)) {
            return $name;
        }

        if ($this->isStrict()) {
            throw new InvalidArgumentException('Requested element key for value %s does not exist.', var_export($value, true));
        }

        return null;
    }

    /**
     * @return bool
     */
    private function isStrict(): bool
    {
        return true === $this->strict;
    }
}
