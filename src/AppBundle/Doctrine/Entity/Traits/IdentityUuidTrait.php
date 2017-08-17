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

use SR\Exception\Logic\LogicException;

trait IdentityUuidTrait
{
    /**
     * @var null|string
     */
    private $uuid;

    /**
     * @param string $uuid
     *
     * @return IdentityUuidTrait
     */
    public function setUuid(string $uuid): IdentityUuidTrait
    {
        if (null !== $this->uuid) {
            throw new LogicException('Cannot overwrite the uuid value of "%s" as property is read-only once set!', $this->uuid);
        }

        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @return bool
     */
    public function hasUuid(): bool
    {
        return null !== $this->uuid;
    }
}
