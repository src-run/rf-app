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
use Rf\AppBundle\Doctrine\Entity\SearchIndexLog;
use SR\Doctrine\ORM\Mapping\Entity;

class SearchIndexLogRepository extends AbstractRepository
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
     * @param string $objectIdentity
     *
     * @return null|SearchIndexLog
     */
    public function findByObjectIdentity(string $objectIdentity): ?SearchIndexLog
    {
        $query = $this
            ->createQueryBuilder('l')
            ->where('l.objectIdentity = :object_identity')
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
     * @param string $objectClass
     * @param string $objectIdentity
     *
     * @return null|SearchIndexLog
     */
    public function findByObjectClassAndId(string $objectClass, string $objectIdentity): ?SearchIndexLog
    {
        $query = $this
            ->createQueryBuilder('l')
            ->where('l.objectClass = :object_class')
            ->andWhere('l.objectIdentity = :object_identity')
            ->setParameters([
                'object_class' => $objectClass,
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
     * @param string $objectClass
     * @param string $objectIdentity
     * @param string $objectHash
     *
     * @return null|SearchIndexLog
     */
    public function findByObjectClassAndIdAndHash(string $objectClass, string $objectIdentity, string $objectHash): ?SearchIndexLog
    {
        $query = $this
            ->createQueryBuilder('l')
            ->where('l.objectClass = :object_class')
            ->andWhere('l.objectIdentity = :object_identity')
            ->andWhere('l.objectHash = :object_hash')
            ->setParameters([
                'object_class' => $objectClass,
                'object_identity' => $objectIdentity,
                'object_hash' => $objectHash,
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
