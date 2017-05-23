<?php

/*
 * This file is part of the `src-run/srw-client-silverpapillon` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Doctrine\Repository;

use Doctrine\ORM\Query;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\Paginator;
use Rf\AppBundle\Doctrine\Entity\Article;

class ArticleRepository extends AbstractRepository
{
    /**
     * @return Query
     */
    public function createFindAllQuery(): Query
    {
        return $this->createQueryBuilder('a')->getQuery();
    }

    /**
     * @param int $id
     *
     * @return Query
     */
    public function createFindByIdQuery(int $id): Query
    {
        return $this
            ->createQueryBuilder('a')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery();
    }

    /**
     * @param Paginator $pager
     * @param int       $page
     * @param int       $limit
     *
     * @return PaginationInterface
     */
    public function findPaginated(Paginator $pager, int $page, int $limit = 10): PaginationInterface
    {
        return $pager->paginate($this->createQueryBuilder('p')->orderBy('p.createdOn', 'DESC'), $page, $limit);
    }

    /**
     * @param string $slug
     * @param string $year
     * @param string $month
     * @param string $day
     *
     * @return Article|null
     */
    public function findByDateAndSlug(string $slug, string $year, string $month, string $day)
    {
        return $this
            ->createQueryBuilder('p')
            ->where('p.slug = :slug')
            ->andWhere("DATE_FORMAT(p.createdOn, '%Y') = :year")
            ->andWhere("DATE_FORMAT(p.createdOn, '%m') = :month")
            ->andWhere("DATE_FORMAT(p.createdOn, '%d') = :day")
            ->setParameters([
                'slug' => $slug,
                'year' => $year,
                'month' => str_pad($month, 2, '0', STR_PAD_LEFT),
                'day' => str_pad($day, 2, '0', STR_PAD_LEFT),
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }
}
