<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Search\Indexing;

use Rf\AppBundle\Component\Search\Indexing\Model\IndexableEntityModel;
use Rf\AppBundle\Doctrine\Entity\Article;
use Rf\AppBundle\Doctrine\Repository\ArticleRepository;

class ArticleProvider implements EntityProviderInterface
{
    /**
     * @var ArticleRepository
     */
    private $repository;

    /**
     * @param ArticleRepository $repository
     */
    public function __construct(ArticleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return \Generator|IndexableEntityModel[]
     */
    public function getModels(): \Generator
    {
        foreach ($this->repository->getUuids() as $uuid) {
            if (null !== $article = $this->repository->findByUuid($uuid)) {
                yield $this->hydrateModel($article);
            }
        }
    }

    /**
     * @param Article $article
     *
     * @return IndexableEntityModel
     */
    private function hydrateModel(Article $article): IndexableEntityModel
    {
        return new IndexableEntityModel(
            $article->getCalledClassName(),
            $article->getIdentity(),
            $article->getUpdated(),
            $article->getSlug(),
            $article->getTitle(),
            $article->getContent()
        );
    }
}
