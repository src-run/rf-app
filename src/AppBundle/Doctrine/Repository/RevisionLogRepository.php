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

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Gedmo\Exception\RuntimeException;
use Gedmo\Exception\UnexpectedValueException;
use Gedmo\Loggable\LoggableListener;
use Gedmo\Tool\Wrapper\EntityWrapper;
use Rf\AppBundle\Doctrine\Entity\RevisionLog;
use SR\Doctrine\ORM\Mapping\EntityInterface;

class RevisionLogRepository extends AbstractRepository
{
    /**
     * Currently used loggable listener
     *
     * @var LoggableListener
     */
    private $listener;

    /**
     * @param EntityInterface $entity
     *
     * @return RevisionLog[]
     */
    public function getLogEntries(EntityInterface $entity): array
    {
        return $this
            ->getLogEntriesQuery($entity)
            ->getResult();
    }

    /**
     * @param EntityInterface $entity
     *
     * @return Query
     */
    public function getLogEntriesQuery(EntityInterface $entity): Query
    {
        $dql = vsprintf('SELECT log FROM %s log WHERE log.objectId = :objectId AND log.objectClass = :objectClass ORDER BY log.version DESC', [
            $this->getClassMetadata()->name
        ]);

        $wrap = new EntityWrapper($entity, $this->_em);

        return $this
            ->_em
            ->createQuery($dql)
            ->setParameters([
                'objectId' => $wrap->getIdentifier(),
                'objectClass' => $wrap->getMetadata()->name,
            ]);
    }

    /**
     * @param EntityInterface $entity
     * @param int             $version
     *
     * @throws UnexpectedValueException
     */
    public function revert(EntityInterface$entity, int $version = 1): void
    {
        $dql = vsprintf('SELECT log FROM %s log WHERE log.objectId = :objectId AND log.objectClass = :objectClass AND log.version <= :version ORDER BY log.version ASC', [
            $this->getClassMetadata()->name
        ]);

        $wrap = new EntityWrapper($entity, $this->_em);

        $logs = $this
            ->_em
            ->createQuery($dql)
            ->setParameters([
                'objectId' => $wrap->getIdentifier(),
                'objectClass' => $wrap->getMetadata()->name,
                'version' => $version,
            ])
            ->getResult();

        if (!$logs) {
            throw new UnexpectedValueException(sprintf('Could not find any log entries under version: %d', $version));
        }

        $config = $this
            ->getLoggableListener()
            ->getConfiguration($this->_em, $wrap->getMetadata()->name);
        $fields = $config['versioned'];
        $filled = false;

        while (($l = array_pop($logs)) && !$filled) {
            if ($data = $l->getData()) {
                foreach ($data as $f => $v) {
                    if (in_array($f, $fields)) {
                        $this->mapValue($wrap->getMetadata(), $f, $v);
                        $wrap->setPropertyValue($f, $v);
                        unset($fields[array_search($f, $fields)]);
                    }
                }
            }

            $filled = count($fields) === 0;
        }
    }

    /**
     * @param ClassMetadata $metadata
     * @param string        $field
     * @param mixed         $value
     */
    protected function mapValue(ClassMetadata $metadata, string $field, &$value): void
    {
        if ($metadata->isSingleValuedAssociation($field)) {
            $value = $value ? $this->_em->getReference($metadata->getAssociationMapping($field)['targetEntity'], $value) : null;
        }
    }

    /**
     * @throws RuntimeException If listener is not found
     *
     * @return LoggableListener
     */
    private function getLoggableListener(): LoggableListener
    {
        if (null !== $this->listener || null !== $this->listener = $this->findLoggableListener()) {
            return $this->listener;
        }

        throw new RuntimeException('The loggable listener could not be initialized/located.');
    }

    /**
     * @return LoggableListener|null
     */
    private function findLoggableListener(): ?LoggableListener
    {
        foreach ($this->_em->getEventManager()->getListeners() as $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof LoggableListener) {
                    return $listener;
                }
            }
        }

        return null;
    }
}
