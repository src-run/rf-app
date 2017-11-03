<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Doctrine\Entity;

use Rf\AppBundle\Doctrine\Entity\Interfaces\ObjectIdentityInterface;
use Rf\AppBundle\Doctrine\Entity\Traits\TimestampableUpdatedTrait;
use SR\Doctrine\ORM\Mapping\IdEntity;

class SearchIndexLog extends IdEntity implements ObjectIdentityInterface
{
    use TimestampableUpdatedTrait;

    /**
     * @var bool
     */
    private $success;

    /**
     * @var string
     */
    private $objectClass;

    /**
     * @var string
     */
    private $objectIdentity;

    /**
     * @var string|null
     */
    private $objectHash;

    public function initializeSuccess(): void
    {
        $this->success = false;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     *
     * @return self
     */
    public function setSuccess(bool $success): self
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getObjectClass(): ? string
    {
        return $this->objectClass;
    }

    /**
     * @param string $objectClass
     *
     * @return self
     */
    public function setObjectClass(string $objectClass) : self
    {
        $this->objectClass = $objectClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getObjectIdentity(): string
    {
        return $this->objectIdentity;
    }

    /**
     * @param string $objectIdentity
     *
     * @return self
     */
    public function setObjectIdentity(string $objectIdentity): self
    {
        $this->objectIdentity = $objectIdentity;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getObjectHash(): ? string
    {
        return $this->objectHash;
    }

    /**
     * @param string|null $objectHash
     *
     * @return self
     */
    public function setObjectHash(string $objectHash = null) : self
    {
        $this->objectHash = $objectHash;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasObjectHash(): bool
    {
        return null !== $this->objectHash;
    }
}
