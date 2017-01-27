<?php
namespace Netlogix\Cqrs\Log;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Doctrine\Common\Persistence\ObjectManager;
use Netlogix\Cqrs\Command\AbstractCommand;
use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class CommandLogger {

	/**
	 * @var ObjectManager
	 * @Flow\Inject
	 */
	protected $entityManager;

	/**
	 * @var CommandLogEntryRepository
	 * @Flow\Inject
	 */
	protected $commandLogEntryRepository;

	/**
	 * Log the given command
	 *
	 * @param AbstractCommand $command
	 * @param \Exception $exception
	 */
	public function logCommand(AbstractCommand $command, \Exception $exception = null) {
		$commandLogEntry = new CommandLogEntry($command);
		$commandLogEntry->setException($exception);
		$this->commandLogEntryRepository->add($commandLogEntry);
		$this->entityManager->flush($commandLogEntry);
	}
}