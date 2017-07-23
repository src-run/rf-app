<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Sitemap\Route;

use Rf\AppBundle\Component\Sitemap\Uri\UriDefinition;
use Rf\AppBundle\Doctrine\Entity\Article;
use Symfony\Component\Routing\Route;

class RouteGeneratorArticlesPermalink extends RouteGeneratorArticlesView
{
    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 200;
    }

    /**
     * @param string $name
     * @param Route  $route
     *
     * @return bool
     */
    public function isSupported(string $name, Route $route): bool
    {
        return false !== strpos($name, 'articles_permalink');
    }

    /**
     * @return \Generator
     */
    protected function getWorkingArgumentSets(): \Generator
    {
        foreach ($this->getArticles() as $article) {
            yield [
                'route_arguments' => [
                    'uuid' => $article->getUuid(),
                ],
                'entity' => $article,
            ];
        }
    }

    /**
     * @param Article $article
     *
     * @return float
     */
    protected function determinePriorityFromEntityAge(Article $article): float
    {
        return 0.5;
    }

    /**
     * @param UriDefinition $definition
     * @param Article       $article
     */
    protected function assignChangeFrequencyForArticle(UriDefinition $definition, Article $article): void
    {
        $definition->setChangeFrequency(UriDefinition::CHANGE_FREQ_NEVER);
    }
}
