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
use Rf\AppBundle\Doctrine\Entity\Interfaces\SluggableInterface;
use SR\Doctrine\ORM\Mapping\IdEntity;

class ArticleTag extends IdEntity implements SluggableInterface
{
    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var PersistentCollection|ArrayCollection|Article[]
     */
    private $articles;

    protected function initializeArticles(): void
    {
        $this->articles = new ArrayCollection();
    }

    /**
     * @param string $slug
     *
     * @return self
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): ? string
    {
        return $this->slug;
    }

    /**
     * @param string $name
     *
     * @return ArticleTag
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $description
     *
     * @return ArticleTag
     */
    public function setDescription(string $description = null): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function hasDescription(): bool
    {
        return null !== $this->description;
    }

    /**
     * @param PersistentCollection|ArrayCollection|Article[] $articles
     *
     * @return ArticleTag
     */
    public function setArticles($articles): self
    {
        $this->articles = $articles;

        return $this;
    }

    /**
     * @return PersistentCollection|ArrayCollection|Article[]
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @return bool
     */
    public function hasArticles(): bool
    {
        return false === $this->articles->isEmpty();
    }
}
