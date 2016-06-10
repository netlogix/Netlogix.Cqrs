<?php
namespace Netlogix\Cqrs\Log;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Netlogix\Cqrs\Command\AbstractCommand;
use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class CommandLogger {

	/**
	 * @var CommandLogEntryRepository
	 * @Flow\Inject
	 */
	protected $commandLogEntryRepository;

	/**
	 * Log the given command
	 *
	 * @param AbstractCommand $command
	 */
	public function logCommand(AbstractCommand $command) {
		$commandLogEntry = new CommandLogEntry($command);
		$this->commandLogEntryRepository->add($commandLogEntry);
	}
}