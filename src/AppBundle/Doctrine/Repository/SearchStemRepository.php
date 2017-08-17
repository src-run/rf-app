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
use Rf\AppBundle\Doctrine\Entity\SearchStem;

class SearchStemRepository extends AbstractRepository
{
    /**
     * @return Query
     */
    public function createFindAllQuery(): Query
    {
        return $this->createQueryBuilder('s')->getQuery();
    }

    /**
     * @param string $stem
     *
     * @return SearchStem|null
     */
    public function findSingleByStem(string $stem): ?SearchStem
    {
        return $this
            ->createQueryBuilder('s')
            ->where('s.stem = :stem')
            ->setParameters([
                'stem' => $stem,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @param string $stem
     *
     * @return SearchStem|null
     */
    public function findByStem(string $stem): ?SearchStem
    {
        $results = $this
            ->createQueryBuilder('s')
            ->where('s.stem = :stem')
            ->setParameters([
                'stem' => $stem,
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
