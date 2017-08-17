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

trait TimestampableCreatedTrait
{
    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @param \DateTime $createdOn
     *
     * @return $this
     */
    public function setCreated(\DateTime $createdOn = null)
    {
        $this->created = $createdOn;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): ? \DateTime
    {
        return $this->created;
    }
}
