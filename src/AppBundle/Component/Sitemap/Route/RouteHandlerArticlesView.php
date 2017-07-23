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
use Rf\AppBundle\Doctrine\Repository\ArticleRepository;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class RouteHandlerArticlesView extends RouteHandlerDefault
{
    /**
     * @var ArticleRepository
     */
    protected $repository;

    /**
     * @param RouterInterface   $router
     * @param ArticleRepository $repository
     */
    public function __construct(RouterInterface $router, ArticleRepository $repository)
    {
        parent::__construct($router);
        $this->repository = $repository;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 201;
    }

    /**
     * @param string $name
     * @param Route  $route
     *
     * @return bool
     */
    public function isSupported(string $name, Route $route): bool
    {
        return false !== strpos($name, 'articles_view');
    }

    /**
     * @param string $name
     * @param Route  $route
     * @param array  $arguments
     *
     * @return null|UriDefinition
     */
    protected function handleArgumentSet(string $name, Route $route, array $arguments): ?UriDefinition
    {
        if (null !== $d = parent::handleArgumentSet($name, $route, $arguments)) {
            if (isset($arguments['entity']) && ($article = $arguments['entity']) instanceof Article) {
                $this->assignLastModifiedForArticle($d, $article);
                $this->assignPriorityForArticle($d, $article);
                $this->assignChangeFrequencyForArticle($d, $article);
            }
        }

        return $d;
    }

    /**
     * @return \Generator
     */
    protected function getArgumentSets(): \Generator
    {
        foreach ($this->getArticles() as $article) {
            yield [
                'route_arguments' => [
                    'year' => $article->getUpdated()->format('Y'),
                    'month' => $article->getUpdated()->format('m'),
                    'day' => $article->getUpdated()->format('d'),
                    'slug' => $article->getSlug(),
                ],
                'entity' => $article,
            ];
        }
    }

    /**
     * @return Article[]
     */
    protected function getArticles(): array
    {
        return $this->repository->findAllRoot();
    }

    /**
     * @param UriDefinition $definition
     * @param Article       $article
     */
    protected function assignLastModifiedForArticle(UriDefinition $definition, Article $article): void
    {
        $definition->setLastModified($article->getUpdated());
        $definition->setLastModifiedPrecise(true);
    }

    /**
     * @param UriDefinition $definition
     * @param Article       $article
     */
    protected function assignPriorityForArticle(UriDefinition $definition, Article $article): void
    {
        $definition->setPriority($this->determinePriorityFromEntityAge($article));
    }

    /**
     * @param Article $article
     *
     * @return float
     */
    protected function determinePriorityFromEntityAge(Article $article): float
    {
        $days = (new \DateTime())->diff($article->getUpdated())->days;
        $priority = 1.0;

        for ($i = 30; $priority > 0.2; $i = $i + 30) {
            $priority -= 0.1;

            if ($i < $days) {
                return $priority;
            }
        }

        return 0.5;
    }

    /**
     * @param UriDefinition $definition
     * @param Article       $article
     */
    protected function assignChangeFrequencyForArticle(UriDefinition $definition, Article $article): void
    {
        $definition->setChangeFrequency(UriDefinition::CHANGE_FREQ_DAILY);
    }
}
