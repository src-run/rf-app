<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Doctrine\Repository;

use Doctrine\ORM\Query;
use Rf\AppBundle\Doctrine\Entity\Article;
use Rf\AppBundle\Doctrine\Entity\SearchIndex;
use Rf\AppBundle\Doctrine\Entity\SearchStem;

class SearchIndexRepository extends AbstractRepository
{
    /**
     * @return Query
     */
    public function createFindAllQuery(): Query
    {
        return $this->createQueryBuilder('s')->getQuery();
    }

    /**
     * @param Article    $article
     * @param SearchStem $stem
     * @param int        $position
     *
     * @return null|SearchIndex
     */
    public function findByArticleStemPosition(Article $article, SearchStem $stem, int $position): ?SearchIndex
    {
        $results = $this
            ->createQueryBuilder('s')
            ->where('s.article = :article')
            ->andWhere('s.stem = :stem')
            ->andWhere('s.position = :position')
            ->setParameters([
                'article' => $article,
                'stem' => $stem,
                'position' => $position,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if (null !== $results && 1 === count($results)) {
            return $results[0];
        }

        return null;
    }
}
