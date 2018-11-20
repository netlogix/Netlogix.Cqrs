<?php

namespace Netlogix\Cqrs\Log;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
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
	 * @param CommandLogEntry $entry
	 * @throws IllegalObjectTypeException
	 * @see Repository::add()
	 * @see Repository::update()
	 */
	public function addOrUpdate(CommandLogEntry $entry)
	{
		if ($this->persistenceManager->isNewObject($entry)) {
			return $this->add($entry);
		} else {
			return $this->update($entry);
		}
	}

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