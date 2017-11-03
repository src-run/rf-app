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

use Rf\AppBundle\Doctrine\Repository\ArticleRepository;

final class ArticleProvider extends AbstractProvider implements EntityProviderInterface
{
    /**
     * @param ArticleRepository $repository
     */
    public function __construct(ArticleRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * @return string[]
     */
    protected function getEntityStemableFieldNames(): array
    {
        return ['slug', 'title', 'content'];
    }
}
