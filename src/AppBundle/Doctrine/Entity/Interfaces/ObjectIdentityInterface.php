<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Doctrine\Entity\Interfaces;

interface ObjectIdentityInterface
{
    /**
     * @return null|string
     */
    public function getObjectClass(): ?string;

    /**
     * @return string
     */
    public function getObjectIdentity(): string;
}
