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
use Rf\AppBundle\Doctrine\Entity\ArticleComment;

class ArticleCommentRepository extends AbstractRepository
{
    /**
     * @return string[]
     */
    public function getUuids(): array
    {
        $query = $this
            ->createQueryBuilder('a')
            ->select('a.uuid')
            ->getQuery();

        return array_map(function (array $row) {
            return $row['uuid'];
        }, $query->getArrayResult());
    }

    /**
     * @param string $uuid
     *
     * @return ArticleComment|null
     */
    public function findByUuid(string $uuid)
    {
        try {
            return $this
                ->createQueryBuilder('p')
                ->where('p.uuid = :uuid')
                ->setParameters([
                    'uuid' => $uuid,
                ])
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
        } catch (ORMException $exception) {
            return null;
        }
    }
}
