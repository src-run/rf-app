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

use SR\Doctrine\ORM\Mapping\IdEntity;

class SearchStem extends IdEntity
{
    /**
     * @var string
     */
    private $stem;

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
}
