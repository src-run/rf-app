<?php

/*
 * This file is part of the `src-run/srw-client-silverpapillon` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Doctrine\Entity;

class Article
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $createdOn;

    /**
     * @var \DateTime
     */
    private $updatedOn;

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \DateTime $createdOn
     *
     * @return self
     */
    public function setCreatedOn(\DateTime $createdOn = null): self
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedOn(): ? \DateTime
    {
        return $this->createdOn;
    }

    /**
     * @param \DateTime $updatedOn
     *
     * @return self
     */
    public function setUpdatedOn(\DateTime $updatedOn = null): self
    {
        $this->updatedOn = $updatedOn;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedOn(): ? \DateTime
    {
        return $this->updatedOn;
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
    public function setTitle(string $title): self
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
     * @param string $content
     *
     * @return self
     */
    public function setContent(string $content): self
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
     * @param int $words
     *
     * @return string
     */
    public function getContentLead(int $words = 100): string
    {
        preg_match(sprintf('/(?:\w+(?:\W+|$)){0,%n}/', $words), $this->getContent(), $matches);

        return isset($matches[0]) && $matches[0] ? $matches[0] : $this->getContent();
    }
}
