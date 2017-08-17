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

use SR\Doctrine\ORM\Mapping\IdEntity;

class SearchIndex extends IdEntity
{
    /**
     * @var int
     */
    private $position;

    /**
     * @var SearchStem
     */
    private $stem;

    /**
     * @var Article
     */
    private $article;

    /**
     * @param int $position
     *
     * @return self
     */
    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param SearchStem $stem
     *
     * @return self
     */
    public function setStem(SearchStem $stem): self
    {
        $this->stem = $stem;

        return $this;
    }

    /**
     * @return null|SearchStem
     */
    public function getStem(): ?SearchStem
    {
        return $this->stem;
    }

    /**
     * @param Article $article
     *
     * @return self
     */
    public function setArticle(Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    /**
     * @return null|Article
     */
    public function getArticle(): ?Article
    {
        return $this->article;
    }
}
