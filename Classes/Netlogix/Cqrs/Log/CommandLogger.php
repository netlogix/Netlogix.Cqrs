<?php

namespace Netlogix\Cqrs\Log;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Neos\Flow\Annotations as Flow;
use Netlogix\Cqrs\Command\AbstractCommand;

/**
 * @Flow\Scope("singleton")
 */
class CommandLogger
{
	/**
	 * @var CommandLogEntryRepository
	 * @Flow\Inject
	 */
	protected $commandLogEntryRepository;

	/**
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 * @Flow\Inject
	 */
	protected $entityManager;

	/**
	 * Log the given command
	 *
	 * @param AbstractCommand $command
	 * @param \Exception $exception
	 */
	public function logCommand(AbstractCommand $command, \Exception $exception = null)
	{
		$commandLogEntry = $this->commandLogEntryRepository->findOneByCommand($command);
		$isNewObject = !$commandLogEntry;

		if ($isNewObject) {
			$commandLogEntry = new CommandLogEntry($command);
		}

		$commandLogEntry->setException($exception === null ? null : new ExceptionData($exception));

		if ($isNewObject) {
			$this->commandLogEntryRepository->add($commandLogEntry);
		} else {
			$this->commandLogEntryRepository->update($commandLogEntry);
		}
		$this->entityManager->flush($commandLogEntry);
	}

}