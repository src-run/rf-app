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

trait IdentityIdTrait
{
    /**
     * @var null|int
     */
    private $id;

    /**
     * @param int $id
     *
     * @return self
     */
    public function setId(int $id)
    {
        if (null !== $this->id) {
            throw new LogicException('Cannot overwrite the id value of "%d" as property is read-only once set!', $this->id);
        }

        $this->id = $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId(): ? int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function hasId(): bool
    {
        return null !== $this->id;
    }
}
