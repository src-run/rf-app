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
use Rf\AppBundle\Doctrine\Entity\Interfaces\IndexableInterface;
use Rf\AppBundle\Doctrine\Entity\Interfaces\SluggableInterface;
use Rf\AppBundle\Doctrine\Entity\Traits\TimestampableTrait;
use SR\Doctrine\ORM\Mapping\UuidEntity;

class Article extends UuidEntity implements SluggableInterface, IndexableInterface
{
    use TimestampableTrait;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $content;

    /**
     * @var PersistentCollection|ArrayCollection|ArticleTag[]
     */
    private $tags;

    /**
     * @var PersistentCollection|ArrayCollection|ArticleComment[]
     */
    private $comments;

    protected function initializeTags(): void
    {
        $this->tags = new ArrayCollection();
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
     * @param string $title
     *
     * @return self
     */
    public function setTitle(string $title) : self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): ? string
    {
        return $this->title;
    }

    /**
     * @return null|string
     */
    public function getSubject() : ? string
    {
        return $this->getTitle();
    }

    /**
     * @return bool
     */
    public function hasSubject() : bool
    {
        return null !== $this->getTitle();
    }

    /**
     * @param string $content
     *
     * @return self
     */
    public function setContent(string $content) : self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): ? string
    {
        return $this->content;
    }

    /**
     * @return bool
     */
    public function hasContent() : bool
    {
        return null !== $this->content;
    }

    /**
     * @param int $words
     *
     * @return string
     */
    public function getContentLead(int $words = 100) : string
    {
        preg_match(sprintf('/(?:\w+(?:\W+|$)){0,%n}/', $words), $this->getContent(), $matches);

        return isset($matches[0]) && $matches[0] ? $matches[0] : $this->getContent();
    }

    /**
     * @param PersistentCollection|ArrayCollection|ArticleTag[] $tags
     *
     * @return self
     */
    public function setTags($tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return ArrayCollection|PersistentCollection|ArticleTag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @return bool
     */
    public function hasTags(): bool
    {
        return false === $this->tags->isEmpty();
    }

    /**
     * @param $comments
     *
     * @return self
     */
    public function setComments($comments): self
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * @return ArrayCollection|PersistentCollection|ArticleComment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @return bool
     */
    public function hasComments(): bool
    {
        return false === $this->tags->isEmpty();
    }
}
