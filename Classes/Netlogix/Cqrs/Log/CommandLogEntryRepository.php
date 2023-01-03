<?php

namespace Netlogix\Cqrs\Log;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Flow\Persistence\Repository;
use Netlogix\Cqrs\Command\AbstractCommand;

/**
 * CommandLogEntries are persisted and updated using DBAL, because:
 * * Using $persistenceManager->persistAll() will persist other modified entities
 * * Using $entityManager->flush($commandLogEntry) will persist the entry, but discard all other working changes
 * * Persisting the log in shutdownObject() will cause problems with sub-processing (Netlogix.Cqrs.RabbitMq)
 *
 * @Flow\Scope("singleton")
 */
class CommandLogEntryRepository extends Repository
{

    /**
     * @var string
     */
    const ENTITY_CLASSNAME = CommandLogEntry::class;

    /**
     * @Flow\Inject(lazy=false)
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param AbstractCommand $command
     * @return CommandLogEntry|null
     */
    public function findOneByCommand(AbstractCommand $command)
    {
        $query = $this->createQuery();
        return $query
            ->matching($query->logicalAnd(
                $query->equals('commandType', $command->getCommandType()),
                $query->equals('commandId', $command->getCommandId())
            ))
            ->execute()
            ->getFirst();
    }

    public function add($object): void
    {
        if (!is_object($object) || !($object instanceof $this->entityClassName)) {
            $type = (is_object($object) ? get_class($object) : gettype($object));
            throw new IllegalObjectTypeException('The value given to add() was ' . $type . ' , however the ' . get_class($this) . ' can only store ' . $this->entityClassName . ' instances.',
                1298403438);
        }
        assert($object instanceof CommandLogEntry);

        $connection = $this->getConnection();
        $classMetaData = $this->entityManager->getClassMetadata(get_class($object));

        $connection->insert($classMetaData->getTableName(), [
            'commandid' => $object->getCommandId(),
            'commandtype' => $object->getCommandType(),
            'executiondateandtime' => $object->getExecutionDateAndTime(),
            'command' => serialize($object->getCommand()),
            'status' => $object->getStatus(),
            'exception' => serialize($object->getException())
        ], [
            Type::STRING,
            Type::STRING,
            Type::DATETIME,
            Type::BLOB,
            Type::INTEGER,
            Type::BLOB,
        ]);
    }

    public function update($object): void
    {
        if (!is_object($object) || !($object instanceof $this->entityClassName)) {
            $type = (is_object($object) ? get_class($object) : gettype($object));
            throw new IllegalObjectTypeException('The value given to update() was ' . $type . ' , however the ' . get_class($this) . ' can only store ' . $this->entityClassName . ' instances.',
                1249479625);
        }
        assert($object instanceof CommandLogEntry);

        $connection = $this->getConnection();
        $classMetaData = $this->entityManager->getClassMetadata(get_class($object));

        $connection->update($classMetaData->getTableName(), [
            'status' => $object->getStatus(),
            'exception' => serialize($object->getException()),
        ], [
            'commandid' => $object->getCommandId()
        ], [
            Type::STRING,
            Type::BLOB,
        ]);
    }

    protected function getConnection(): Connection
    {
        assert($this->entityManager instanceof EntityManager);

        return $this->entityManager->getConnection();
    }

}