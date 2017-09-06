<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Doctrine\Entity\Traits;

trait TimestampableUpdatedTrait
{
    /**
     * @var \DateTime
     */
    private $updated;

    /**
     * @param \DateTime $updated
     *
     * @return $this
     */
    public function setUpdated(\DateTime $updated = null)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated() : ? \DateTime
    {
        return $this->updated;
    }

    /**
     * @return bool
     */
    public function hasUpdated(): bool
    {
        return null !== $this->updated;
    }
}
