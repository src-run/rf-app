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

use Rf\AppBundle\Doctrine\Entity\Interfaces\SluggableInterface;
use Rf\AppBundle\Doctrine\Entity\Traits\TimestampableCreatedTrait;
use SR\Doctrine\ORM\Mapping\UuidEntity;

class ArticleComment extends UuidEntity implements SluggableInterface
{
    use TimestampableCreatedTrait;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $authorName;

    /**
     * @var string
     */
    private $authorEmail;

    /**
     * @var Article
     */
    private $article;

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
     * @param string $authorName
     *
     * @return self
     */
    public function setAuthorName(string $authorName) : self
    {
        $this->authorName = $authorName;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAuthorName(): ? string
    {
        return $this->authorName;
    }

    /**
     * @param string $authorEmail
     *
     * @return self
     */
    public function setAuthorEmail(string $authorEmail) : self
    {
        $this->authorEmail = $authorEmail;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAuthorEmail(): ? string
    {
        return $this->authorEmail;
    }

    /**
     * @param Article $article
     *
     * @return self
     */
    public function setArticle(Article $article) : self
    {
        $this->article = $article;

        return $this;
    }

    /**
     * @return null|Article
     */
    public function getArticle(): ? Article
    {
        return $this->article;
    }
}
