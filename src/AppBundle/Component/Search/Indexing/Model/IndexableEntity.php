<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Search\Indexing\Model;

use SR\Exception\Logic\InvalidArgumentException;

class IndexableEntity
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $identity;

    /**
     * @var \DateTime|null
     */
    private $datetime;

    /**
     * @var string[]
     */
    private $stemable;

    /**
     * @param string         $className
     * @param string|int     $identity
     * @param \DateTime|null $datetime
     * @param string[]       ...$stemable
     */
    public function __construct(string $className, $identity, \DateTime $datetime = null, string ...$stemable)
    {
        if (!is_string($identity) && !is_int($identity)) {
            throw new InvalidArgumentException('Object identity value must be either a string or an int, got "%s"', gettype($identity));
        }

        $this->className = $className;
        $this->identity = $identity;
        $this->datetime = $datetime;
        $this->stemable = $stemable;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return string|int
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @return bool
     */
    public function hasDatetime(): bool
    {
        return null !== $this->datetime;
    }

    /**
     * @return \DateTime|null
     */
    public function getDatetime(): ? \DateTime
    {
        return $this->datetime;
    }

    /**
     * @return array
     */
    public function getStemable() : array
    {
        return $this->stemable;
    }

    /**
     * @param string|null $glue
     *
     * @return string
     */
    public function getStemableImploded(string $glue = null): string
    {
        return implode($glue ?? ' ', $this->getStemable());
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        $hashable = [
            $this->getClassName(),
            (string) $this->getIdentity(),
        ];

        if ($this->hasDatetime()) {
            $hashable[] = $this->getDatetime()->format('r');
        }

        return hash('sha256', implode(':', array_merge($hashable, $this->getStemable())));
    }
}
