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

use Doctrine\ORM\ORMException;
use Rf\AppBundle\Doctrine\Entity\SearchStem;

class SearchStemRepository extends AbstractRepository
{
    /**
     * @return int[]
     */
    public function getIdsWithNoIndices(): array
    {
        $query = $this
            ->createQueryBuilder('s')
            ->select('s', 'indices')
            ->leftJoin('s.indices', 'indices')
            ->getQuery();

        $rows = array_filter($query->getArrayResult(), function (array $row) {
            return 0 === count($row['indices']);
        });

        return array_map(function (array $row) {
            return $row['id'];
        }, $rows);
    }

    /**
     * @param int $id
     *
     * @return SearchStem|null
     */
    public function findById(int $id): ?SearchStem
    {
        $query = $this
            ->createQueryBuilder('s')
            ->where('s.id = :id')
            ->setParameters([
                'id' => $id,
            ])
            ->setMaxResults(1)
            ->getQuery();

        try {
            return $query->getSingleResult();
        } catch (ORMException $exception) {
            return null;
        }
    }

    /**
     * @param string $stem
     *
     * @return SearchStem|null
     */
    public function findByStem(string $stem): ?SearchStem
    {
        $query = $this
            ->createQueryBuilder('s')
            ->where('s.stem = :stem')
            ->setParameters([
                'stem' => $stem,
            ])
            ->setMaxResults(1)
            ->getQuery();

        try {
            return $query->getSingleResult();
        } catch (ORMException $exception) {
            return null;
        }
    }
}
