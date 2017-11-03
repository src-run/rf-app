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

use Rf\AppBundle\Doctrine\Entity\Traits\TimestampableCreatedTrait;
use SR\Doctrine\ORM\Mapping\UuidEntity;

class RevisionLog extends UuidEntity
{
    use TimestampableCreatedTrait;

    /**
     * @var string
     */
    private $action;

    /**
     * @var string
     */
    private $objectClass;

    /**
     * @var string|null
     */
    private $objectId;

    /**
     * @var string|null
     */
    private $username;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var int
     */
    private $version;

    /**
     * @return null|string
     */
    public function getAction(): ? string
    {
        return $this->action;
    }

    /**
     * @param string $action
     *
     * @return self
     */
    public function setAction(string $action) : self
    {
        $this->action = $action;

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
     * @param string|null $objectClass
     *
     * @return self
     */
    public function setObjectClass(string $objectClass = null) : self
    {
        $this->objectClass = $objectClass;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getObjectId(): ? string
    {
        return $this->objectId;
    }

    /**
     * @param string $objectId
     *
     * @return self
     */
    public function setObjectId(string $objectId) : self
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getUsername(): ? string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     *
     * @return self
     */
    public function setUsername(string $username = null) : self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return RevisionLog
     */
    public function setLoggedAt(): self
    {
        return $this;
    }

    /**
     * @return array|null
     */
    public function getData(): ? array
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return self
     */
    public function setData(array $data) : self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getVersion(): ? int
    {
        return $this->version;
    }

    /**
     * @param int $version
     *
     * @return self
     */
    public function setVersion(int $version) : self
    {
        $this->version = $version;

        return $this;
    }
}
