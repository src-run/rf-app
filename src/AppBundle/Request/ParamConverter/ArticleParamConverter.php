<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Request\ParamConverter;

use Doctrine\ORM\EntityManager;
use Rf\AppBundle\Doctrine\Entity\Article;
use Rf\AppBundle\Doctrine\Repository\ArticleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArticleParamConverter implements ParamConverterInterface
{
    /**
     * @var string[]
     */
    private static $searchFields = [
        'slug',
        'year',
        'month',
        'day'
    ];

    /**
     * @var ArticleRepository
     */
    private $repository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param ArticleRepository $repository
     * @param EntityManager     $entityManager
     */
    public function __construct(ArticleRepository $repository, EntityManager $entityManager)
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Request        $request
     * @param ParamConverter $configuration
     *
     * @return bool
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $request->attributes->set($configuration->getName(), $this->find($request));

        return true;
    }

    /**
     * @param ParamConverter $configuration
     *
     * @return bool
     */
    public function supports(ParamConverter $configuration): bool
    {
        return $this->entityManager->isOpen() && $configuration->getClass() === Article::class;
    }

    /**
     * @param Request        $request
     *
     * @return Article
     */
    private function find(Request $request): Article
    {
        try {
            return $this->repository->findByDateAndSlug(...$this->resolveSearchFields($request));
        } catch (\Exception $exception) {
            throw new NotFoundHttpException('Could not locate the request article.', $exception);
        }
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function resolveSearchFields(Request $request): array
    {
        return array_map(function (string $field) use ($request) {
            return $request->attributes->has($field) ? $request->attributes->get($field) : null;
        }, static::$searchFields);
    }
}
