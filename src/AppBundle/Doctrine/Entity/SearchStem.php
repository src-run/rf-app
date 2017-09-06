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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use SR\Doctrine\ORM\Mapping\IdEntity;

class SearchStem extends IdEntity
{
    /**
     * @var string
     */
    private $stem;

    /**
     * @var PersistentCollection|ArrayCollection|SearchIndex[]
     */
    private $indices;

    /**
     * @return self
     */
    public function initializeIndices(): self
    {
        $this->indices = new ArrayCollection();

        return $this;
    }

    /**
     * @param string $stem
     *
     * @return self
     */
    public function setStem(string $stem): self
    {
        $this->stem = $stem;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStem(): ?string
    {
        return $this->stem;
    }

    /**
     * @param PersistentCollection|ArrayCollection|SearchIndex[] $indices
     *
     * @return self
     */
    public function setIndices($indices): self
    {
        $this->indices = $indices;

        return $this;
    }

    /**
     * @return PersistentCollection|ArrayCollection|SearchIndex[]
     */
    public function getIndices()
    {
        return $this->indices;
    }

    /**
     * @return bool
     */
    public function hasIndices(): bool
    {
        return !$this->indices->isEmpty();
    }
}
