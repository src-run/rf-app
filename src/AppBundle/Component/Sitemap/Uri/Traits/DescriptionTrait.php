<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Sitemap\Uri\Traits;

use SR\Exception\Logic\InvalidArgumentException;

trait DescriptionTrait
{
    /**
     * @var string|null
     */
    private $description;

    /**
     * @param string|null $description
     *
     * @return self
     */
    public function setDescription(string $description = null): self
    {
        $description = $this->toHtmlSpecialChars($description);

        if (strlen($description) > 2048) {
            throw new InvalidArgumentException('Description cannot exceed 2048 characters (got %d).', strlen($description));
        }

        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasDescription(): bool
    {
        return $this->description !== null;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ? string
    {
        return $this->description;
    }
}
