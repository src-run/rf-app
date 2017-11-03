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

use CaseHelper\CaseHelperFactory;
use Rf\AppBundle\Component\Search\Indexing\Model\IndexableEntity;
use Rf\AppBundle\Doctrine\Repository\AbstractRepository;
use SR\Doctrine\Exception\OrmException;
use SR\Doctrine\ORM\Mapping\Entity;
use SR\Exception\Runtime\RuntimeException;

abstract class AbstractProvider implements EntityProviderInterface
{
    /**
     * @var AbstractRepository
     */
    private $repository;

    /**
     * @param AbstractRepository $repository
     */
    public function __construct(AbstractRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        $name = preg_replace('{Provider$}', '', $this->getClass(false));

        try {
            return CaseHelperFactory::make(CaseHelperFactory::INPUT_TYPE_PASCAL_CASE)->toKebabCase($name);
        } catch (\Exception $exception) {
            return $name;
        }
    }

    /**
     * @param bool $qualified
     *
     * @return string
     */
    public function getClass(bool $qualified = true): string
    {
        $r = new \ReflectionObject($this);

        return $qualified ? $r->getName() : $r->getShortName();
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->repository->getNumberOfElement();
    }

    /**
     * @return string[]|int[]
     */
    public function getIdentities(): array
    {
        return $this->repository->getIdentities();
    }

    /**
     * @return Entity[]
     */
    public function getEntities(): array
    {
        $entities = array_map(function ($identity) {
            return $this->find($identity);
        }, $this->getIdentities());

        return array_filter($entities, function ($entity) {
            return $entity instanceof Entity;
        });
    }

    /**
     * @return IndexableEntity[]
     */
    public function getIndexableModels(): array
    {
        return array_map(function (Entity $entity) {
            return $this->hydrateIndexableEntityModel($entity);
        }, $this->getEntities());
    }

    /**
     * @return \Generator|string[]
     */
    public function forEachIdentities(): \Generator
    {
        foreach ($this->getIdentities() as $identity) {
            yield $identity;
        }
    }

    /**
     * @return \Generator|Entity[]
     */
    public function forEachEntities(): \Generator
    {
        foreach ($this->getIdentities() as $identity) {
            yield $this->find($identity);
        }
    }

    /**
     * @return \Generator|IndexableEntity[]
     */
    public function forEachIndexableModels(): \Generator
    {
        foreach ($this->forEachEntities() as $entity) {
            yield $this->hydrateIndexableEntityModel($entity);
        }
    }

    /**
     * @param Entity $entity
     *
     * @return IndexableEntity
     */
    private function hydrateIndexableEntityModel(Entity $entity): IndexableEntity
    {
        return new IndexableEntity(
            $this->getEntityClassName($entity),
            $this->getEntityIdentity($entity),
            $this->getEntityDateTime($entity),
            ...$this->getEntityStemable($entity)
        );
    }

    /**
     * @param string|int $identity
     *
     * @return null|Entity
     */
    private function find($identity): ? Entity
    {
        return $this->repository->findOneByIdentity($identity);
    }

    /**
     * @param Entity $entity
     *
     * @return string
     */
    private function getEntityClassName(Entity $entity) : string
    {
        return $this->repository->getClassName();
    }

    /**
     * @param Entity $entity
     *
     * @return mixed
     */
    private function getEntityIdentity(Entity $entity)
    {
        try {
            return $entity->getIdentity();
        } catch (OrmException $exception) {
            throw new RuntimeException('Cannot create indexable entity model without an entity identity field!', $exception);
        }
    }

    /**
     * @param Entity $entity
     *
     * @return \DateTime|null
     */
    private function getEntityDateTime(Entity $entity): ? \DateTime
    {
        foreach (['getUpdated', 'getUpdatedOn', 'getCreated', 'getCreatedOn'] as $method) {
            if (method_exists($entity, $method)) {
                return $entity->{$method}();
            }
        }

        return null;
    }

    /**
     * @param Entity $entity
     *
     * @return string[]
     */
    private function getEntityStemable(Entity $entity) : array
    {
        $stemable = array_map(function (string $field) use ($entity) {
            return $this->resolveEntityFieldValue($entity, $field);
        }, $this->getEntityStemableFieldNames());

        return array_filter($stemable, function (string $value = null) {
            return null !== $value;
        });
    }

    /**
     * @param Entity $entity
     * @param string $field
     *
     * @return string|null
     */
    private function resolveEntityFieldValue(Entity $entity, string $field): ? string
    {
        if (method_exists($entity, $method = sprintf('get%s', ucfirst($field)))) {
            return (string) $entity->{$method}();
        }

        try {
            $p = (new \ReflectionObject($entity))->getProperty($field);
            $p->setAccessible(true);

            return (string) $p->getValue($entity);
        } catch (\ReflectionException $exception) {
            return null;
        }
    }

    /**
     * @return string[]
     */
    abstract protected function getEntityStemableFieldNames() : array;
}
