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

use Rf\AppBundle\Component\HttpFoundation\Request\RequestAttributesResolver;
use Rf\AppBundle\Doctrine\Entity\Article;
use Rf\AppBundle\Doctrine\Repository\ArticleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArticleParamConverter implements ParamConverterInterface
{
    /**
     * @var array[]
     */
    private static $attributeFieldsMapping = [
        'findByDateAndSlug' => ['slug', 'year', 'month', 'day'],
        'findByUuid' => ['uuid',],
    ];

    /**
     * @var ArticleRepository
     */
    private $repository;

    /**
     * @param ArticleRepository $repository
     */
    public function __construct(ArticleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Request        $request
     * @param ParamConverter $configuration
     *
     * @return bool
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $article = $this->find($request);
        $request->attributes->set($configuration->getName(), $article);

        return $article ? true : false;
    }

    /**
     * @param ParamConverter $configuration
     *
     * @return bool
     */
    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() === Article::class;
    }

    /**
     * @param Request $request
     *
     * @return Article|null
     */
    private function find(Request $request): ?Article
    {
        $resolver = new RequestAttributesResolver($request);

        foreach (static::$attributeFieldsMapping as $method => $fields) {
            if ($resolver->has($fields)) {
                return $this->findUsingMethod($method, $resolver->get($fields));
            }
        }

        return null;
    }

    /**
     * @param string $method
     * @param array  $resolvedValues
     *
     * @return null|Article
     */
    private function findUsingMethod(string $method, array $resolvedValues): ?Article
    {
        try {
            return $this->repository->{$method}(...$resolvedValues);
        } catch (\Exception $exception) {
            throw new NotFoundHttpException('Unable to locate requested article.', $exception);
        }
    }
}
