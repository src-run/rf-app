<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Tests;

use Rf\AppBundle\Doctrine\Entity\Article;
use Rf\AppBundle\Doctrine\Repository\ArticleRepository;

trait RepositoryTestTrait
{
    /**
     * @var ArticleRepository
     */
    private $repository;

    private function autoSetUp100Repository()
    {
        $this->repository = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Article::class);
    }
}
