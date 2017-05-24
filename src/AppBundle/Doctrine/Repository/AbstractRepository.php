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

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parameter;
use SR\Exception\Exception;
use SR\Exception\ExceptionInterface;
use SR\Util\Info\ClassInfo;
use SR\Util\Transform\StringTransform;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractRepository extends EntityRepository
{
    /**
     * @var bool
     */
    private const CACHE_ENABLED = true;

    /**
     * @var int
     */
    private const DEFAULT_TTL = 300;

    /**
     * @param string|null $indexBy
     *
     * @return array
     */
    public function findAllRoot($indexBy = null)
    {
        $data = $this->createQueryBuilder('object')->getQuery()->getResult();

        if ($data && $indexBy) {
            $data = $this->sortCollection($data, $indexBy);
        }

        return $data;
    }

    /**
     * @param string|null $indexBy
     *
     * @return array
     */
    public function findAllArray($indexBy = null): array
    {
        $data = $this->createQueryBuilder('object', $indexBy)->getQuery()->getArrayResult();

        if ($data && $indexBy) {
            $data = $this->sortCollection($data, $indexBy);
        }

        return $data;
    }

    /**
     * @param array       $criteria
     * @param array|null  $orderBy
     * @param int|null    $limit
     * @param int|null    $offset
     * @param string|null $indexBy
     * @return array
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null, $indexBy = null): array
    {
        $data = parent::findBy($criteria, $orderBy, $limit, $offset);

        if ($data && $indexBy) {
            $data = $this->sortCollection($data, $indexBy);
        }

        return $data;
    }

    /**
     * @param string|null $indexBy
     *
     * @return array
     */
    public function findAll($indexBy = null): array
    {
        $data = $this->findBy([]);

        if ($data && $indexBy) {
            $data = $this->sortCollection($data, $indexBy);
        }

        return $data;
    }

    /**
     * @return int
     */
    public function getAutoIncrement(): int
    {
        $statement = $this->getEntityManager()->getConnection()->prepare(
            sprintf('show table status like "%s"', $this->getClassMetadata()->getTableName())
        );
        $statement->execute();

        return (int) $statement->fetch()['Auto_increment'];
    }

    /**
     * @return int
     */
    public function getNumberOfElement(): int
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('count(a)')
            ->from($this->getEntityName(), 'a')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array
     */
    public function getIds(): array
    {
        $collection = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(sprintf('entity.%s', $this->getClassMetadata()->getSingleIdentifierFieldName()))
            ->from($this->getEntityName(), 'entity')
            ->getQuery()
            ->getArrayResult();

        return array_map(function (array $element) {
            return $element[$this->getClassMetadata()->getSingleIdentifierFieldName()];
        }, $collection);
    }

    /**
     * @param array       $collection
     * @param null|string $indexBy
     *
     * @return array
     */
    public function sortCollection($collection, $indexBy = null)
    {
        $sortedCollection = [];
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $currentObject = current($collection);

        if ($indexBy === '_primary_id') {
            $indexBy = $this->getClassMetadata()->getSingleIdentifierFieldName();
        }

        if (is_object($currentObject) && property_exists($currentObject, $indexBy) && method_exists($currentObject, 'get'.ucfirst($indexBy))) {
            foreach ($collection as $entity) {
                $sortedCollection[$propertyAccessor->getValue($entity, $indexBy)] = $entity;
            }
        }

        if (is_array($currentObject) && array_key_exists($indexBy, $currentObject)) {
            foreach ($collection as $array) {
                $sortedCollection[$array[$indexBy]] = $array;
            }
        }

        return count($sortedCollection) === 0 ? $collection : $sortedCollection;
    }

    /**
     * @param callable|null $config
     * @param string|null   $alias
     *
     * @return Query
     */
    protected function getQuery(callable $config = null, $alias = null)
    {
        if ($alias === null) {
            $alias = $this->getAliasFromEntityName();
        }

        $builder = $this->createQueryBuilder($alias);

        if (is_callable($config)) {
            call_user_func_array($config, [$builder, $alias]);
        }

        return $builder->getQuery();
    }

    /**
     * @param callable|null $build
     * @param bool          $single
     * @param int|null      $ttl
     *
     * @return mixed
     */
    protected function getResult(callable $build = null, $single = false, $ttl = null)
    {
        $query = $this->getQuery($build);

        if (self::CACHE_ENABLED) {
            $index = $this->getCacheKey($query);
            $cache = $this->getCacheDriver();

            if ($cache->contains($index)) {
                return $cache->fetch($index);
            }
        }

        if ($single === true) {
            $result = $query->getSingleResult();
        } else {
            $result = $query->getResult();
        }

        if (isset($cache) && isset($index)) {
            $cache->save($index, $result, $ttl ?: self::DEFAULT_TTL);
        }

        return $result;
    }

    /**
     * @param Query $query
     *
     * @return string
     */
    private function getCacheKey(Query $query)
    {
        $k = sprintf('silver-papillon-%s-%s-', $this->getClassNameShort(), $query->getDQL());

        foreach ($query->getParameters() as $parameter) {
            $k .= $this->getCacheKeyPartFromParameter($parameter).'-';
        }

        $transformer = new StringTransform(strtolower(substr($k, 0, strlen($k) - 1)));

        return $transformer->toAlphanumericAndDashes();
    }

    /**
     * @param Parameter $parameter
     *
     * @throws ExceptionInterface
     *
     * @return string
     */
    private function getCacheKeyPartFromParameter(Parameter $parameter)
    {
        $name = $parameter->getName();
        $value = $parameter->getValue();

        if (is_object($value) && !method_exists($value, '__toString')) {
            throw Exception::create('Could not convert parameter to string for doctrin result cache in %s', $this->getClassName());
        }

        return $name.'='.(string) $value;
    }

    /**
     * @return CacheProvider
     */
    private function getCacheDriver()
    {
        static $cacheDriver;

        if ($cacheDriver === null) {
            $cacheDriver = new ApcuCache();
        }

        return $cacheDriver;
    }

    /**
     * @return string
     */
    private function getAliasFromEntityName()
    {
        return strtolower(substr($this->getClassNameShort(), 0, 1));
    }

    /**
     * @return string
     */
    private function getClassNameShort()
    {
        return ClassInfo::getNameShort($this->getClassName());
    }
}
