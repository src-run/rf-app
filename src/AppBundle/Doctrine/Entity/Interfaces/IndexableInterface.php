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

interface IndexableInterface
{
    /**
     * @return \DateTime|null
     */
    public function getUpdated() : ? \DateTime;

    /**
     * @return bool
     */
    public function hasUpdated() : bool;

    /**
     * @return string
     */
    public function getContent(): ? string;

    /**
     * @return bool
     */
    public function hasContent() : bool;

    /**
     * @return null|string
     */
    public function getSubject(): ? string;

    /**
     * @return bool
     */
    public function hasSubject() : bool;
}
