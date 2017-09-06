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

class IndexableEntityModel
{
    /**
     * @var string
     */
    private $objectClass;

    /**
     * @var string
     */
    private $objectIdentity;

    /**
     * @var \DateTime|null
     */
    private $updated;

    /**
     * @var string[]
     */
    private $stemable;

    /**
     * @param string         $objectClass
     * @param string         $objectIdentity
     * @param \DateTime|null $updated
     * @param string[]       ...$stemable
     */
    public function __construct(string $objectClass, string $objectIdentity, \DateTime $updated, string ...$stemable)
    {
        $this->objectClass = $objectClass;
        $this->objectIdentity = $objectIdentity;
        $this->updated = $updated;
        $this->stemable = $stemable;
    }

    /**
     * @return string
     */
    public function getObjectClass(): string
    {
        return $this->objectClass;
    }

    /**
     * @return string
     */
    public function getObjectIdentity(): string
    {
        return $this->objectIdentity;
    }

    /**
     * @return bool
     */
    public function hasUpdated(): bool
    {
        return null !== $this->updated;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdated(): ?\DateTime
    {
        return $this->updated;
    }

    /**
     * @return array
     */
    public function getStemmable(): array
    {
        return $this->stemable;
    }

    /**
     * @return string
     */
    public function getStemmableString(): string
    {
        return implode(' ', $this->stemable);
    }

    /**
     * @return string
     */
    public function getObjectHash(): string
    {
        return hash('sha256', implode(':', array_merge([
            $this->objectClass,
            $this->objectIdentity,
            $this->updated->format('r'),
        ], $this->stemable)));
    }
}
