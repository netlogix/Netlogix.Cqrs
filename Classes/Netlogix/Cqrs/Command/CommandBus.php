<?php
namespace Netlogix\Cqrs\Command;

/*
 * This file is part of the Netlogix. package.
 */

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
	 * @param CommandInterface $command
	 */
	public function delegate(CommandInterface $command) {
		$this->commandHandler->handle($command);
	}
}