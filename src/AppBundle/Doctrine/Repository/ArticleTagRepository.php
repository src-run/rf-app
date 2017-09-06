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

use Rf\AppBundle\Doctrine\Entity\ArticleTag;

class ArticleTagRepository extends AbstractRepository
{
    /**
     * @return string[]
     */
    public function getSlugs(): array
    {
        $query = $this
            ->createQueryBuilder('t')
            ->select('t.slug')
            ->getQuery();

        return array_map(function (array $row) {
            return $row['slug'];
        }, $query->getArrayResult());
    }

    /**
     * @param string $slug
     *
     * @return ArticleTag|null
     */
    public function findBySlug(string $slug)
    {
        return $this
            ->createQueryBuilder('t')
            ->where('t.slug = :slug')
            ->setParameters([
                'slug' => $slug,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }
}
