<?php
namespace Netlogix\Cqrs\Log;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Netlogix\Cqrs\Command\Command;
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
	 * @param Command $command
	 */
	public function logCommand(Command $command) {
		$commandLogEntry = new CommandLogEntry($command);
		$this->commandLogEntryRepository->add($commandLogEntry);
	}
}