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
use Rf\AppBundle\Doctrine\Entity\SearchIndex;
use Rf\AppBundle\Doctrine\Entity\SearchStem;

class SearchIndexRepository extends AbstractRepository
{
    /**
     * @return string[]
     */
    public function getObjectIdentities(): array
    {
        $query = $this
            ->createQueryBuilder('l')
            ->select('l.objectIdentity')
            ->getQuery();

        return array_map(function (array $row) {
            return $row['objectIdentity'];
        }, $query->getArrayResult());
    }

    /**
     * @param int $id
     *
     * @return null|SearchIndex
     */
    public function findById(int $id): ? SearchIndex
    {
        $query = $this
            ->createQueryBuilder('i')
            ->where('i.id = :id')
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
     * @param string $objectIdentity
     *
     * @return null|SearchIndex
     */
    public function findByObjectIdentity(string $objectIdentity) : ? SearchIndex
    {
        $query = $this
            ->createQueryBuilder('i')
            ->where('i.objectIdentity = :object_identity')
            ->setParameters([
                'object_identity' => $objectIdentity,
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
     * @param string     $objectClass
     * @param string     $objectIdentity
     * @param SearchStem $stem
     * @param int        $position
     *
     * @return null|SearchIndex
     */
    public function findByObjectAndStemAndPosition(string $objectClass, string $objectIdentity, SearchStem $stem, int $position) : ? SearchIndex
    {
        $query = $this
            ->createQueryBuilder('i')
            ->where('i.objectClass = :object_class')
            ->andWhere('i.objectIdentity = :object_identity')
            ->andWhere('i.stem = :stem')
            ->andWhere('i.position = :position')
            ->setParameters([
                'object_class' => $objectClass,
                'object_identity' => $objectIdentity,
                'stem' => $stem,
                'position' => $position,
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
     * @param string $objectClass
     * @param string $objectIdentity
     * @param SearchIndex[] ...$indices
     *
     * @return SearchIndex[]
     */
    public function findByObjectAndNotInSet(string $objectClass, string $objectIdentity, SearchIndex ...$indices) : array
    {
        $query = $this
            ->createQueryBuilder('i')
            ->select('i.id')
            ->where('i.objectClass = :object_class')
            ->andWhere('i.objectIdentity = :object_identity')
            ->setParameters([
                'object_class' => $objectClass,
                'object_identity' => $objectIdentity,
            ])
            ->getQuery();

        try {
            $results = array_map(function (array $row) {
                return $row['id'];
            }, $query->getArrayResult());
        } catch (ORMException $exception) {
            return [];
        }

        foreach ($indices as $index) {
            if (false !== $key = array_search($index->getId(), $results)) {
                unset($results[$key]);
            }
        }

        return array_filter(array_map(function (int $id) {
            return $this->findById($id);
        }, $results), function ($entity) {
            return null !== $entity;
        });
    }
}
