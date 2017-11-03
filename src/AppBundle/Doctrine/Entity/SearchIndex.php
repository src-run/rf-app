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
use SR\Doctrine\ORM\Mapping\IdEntity;

class SearchIndex extends IdEntity implements ObjectIdentityInterface
{
    /**
     * @var int
     */
    private $position;

    /**
     * @var SearchStem
     */
    private $stem;

    /**
     * @var string
     */
    private $objectClass;

    /**
     * @var string
     */
    private $objectIdentity;

    /**
     * @param int $position
     *
     * @return self
     */
    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPosition(): ? int
    {
        return $this->position;
    }

    /**
     * @param SearchStem $stem
     *
     * @return self
     */
    public function setStem(SearchStem $stem) : self
    {
        $this->stem = $stem;

        return $this;
    }

    /**
     * @return null|SearchStem
     */
    public function getStem(): ? SearchStem
    {
        return $this->stem;
    }

    /**
     * @return null|string
     */
    public function getObjectClass() : ? string
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
}
