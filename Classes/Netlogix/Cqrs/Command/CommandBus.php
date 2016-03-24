<?php
namespace Netlogix\Cqrs\Command;

/*
 * This file is part of the Netlogix. package.
 */

use Netlogix\Cqrs\Log\CommandLogger;
use TYPO3\Flow\Annotations as Flow;

/**
 * The command bus accepts commands and delegates execution of given commands
 */
class CommandBus {

	/**
	 * @var CommandHandlerInterface
	 * @Flow\Inject
	 */
	protected $commandHandler;

	/**
	 * @var CommandLogger
	 * @Flow\Inject
	 */
	protected $commandLogger;

	/**
	 * @param CommandInterface $command
	 */
	public function delegate(CommandInterface $command) {
		$this->commandHandler->handle($command);
		if ($command instanceof Command) {
			$this->commandLogger->logCommand($command);
		}
	}
}