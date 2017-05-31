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
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\VarDumper\VarDumper;

class ArticleParamConverter implements ParamConverterInterface
{
    /**
     * @var array[]
     */
    private static $fieldSetMethodMaps = [
        'findByDateAndSlug' => [
            'slug',
            'year',
            'month',
            'day',
        ],
        'findByUuid' => [
            'uuid',
        ]
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
        if (null === $article = $this->findArticleUsingAttributes($request->attributes)) {
            return false;
        }

        $request->attributes->set($configuration->getName(), $article);

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
     * @param ParameterBag $attributes
     *
     * @return Article|null
     */
    private function findArticleUsingAttributes(ParameterBag $attributes): ?Article
    {
        try {
            foreach (static::$fieldSetMethodMaps as $method => $fields) {
                if ($this->hasAttributes($attributes, $fields)) {
                    return call_user_func_array([$this->repository, $method], $this->resolveAttributes($attributes, $fields));
                }
            }
        } catch (\Exception $exception) {
            throw new NotFoundHttpException('Unable to locate requested article.', $exception);
        }

        return null;
    }

    /**
     * @param ParameterBag $attributes
     * @param string[]     $fields
     *
     * @return array
     */
    private function resolveAttributes(ParameterBag $attributes, array $fields): array
    {
        return array_map(function (string $field) use ($attributes) {
            return $attributes->get($field);
        }, $fields);
    }

    /**
     * @param ParameterBag $attributes
     * @param array        $fields
     *
     * @return bool
     */
    private function hasAttributes(ParameterBag $attributes, array $fields): bool
    {
        foreach ($fields as $f) {
            if (!$attributes->has($f)) {
                return false;
            }
        }

        return true;
    }
}
