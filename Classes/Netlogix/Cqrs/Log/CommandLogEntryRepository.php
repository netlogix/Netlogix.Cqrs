<?php

namespace Netlogix\Cqrs\Log;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Repository;
use Netlogix\Cqrs\Command\AbstractCommand;

/**
 * @Flow\Scope("singleton")
 */
class CommandLogEntryRepository extends Repository
{
    /**
     * @var string
     */
    const ENTITY_CLASSNAME = CommandLogEntry::class;

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
}