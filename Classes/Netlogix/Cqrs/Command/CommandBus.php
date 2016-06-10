<?php
namespace Netlogix\Cqrs\Command;

/*
 * This file is part of the Netlogix. package.
 */

use Netlogix\Cqrs\Log\CommandLogger;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Flow\Reflection\ReflectionService;

/**
 * The command bus accepts commands and delegates execution of given commands
 */
class CommandBus {

	/**
	 * @var CommandHandlerInterface[]
	 */
	protected $commandHandlers;

	/**
	 * @var CommandLogger
	 * @Flow\Inject
	 */
	protected $commandLogger;

	/**
	 * @var ReflectionService
	 * @Flow\Inject
	 */
	protected $reflectionService;

	/**
	 * @var ObjectManagerInterface
	 * @Flow\Inject
	 */
	protected $objectManager;

	/**
	 * @param CommandInterface $command
	 */
	public function delegate(CommandInterface $command) {
		$this->initializeCommandHandlers();
		foreach ($this->commandHandlers as $commandHandler) {
			if ($commandHandler->canHandle($command)) {
				$commandHandler->handle($command);
			}
		}
		if ($command instanceof Command) {
			$this->commandLogger->logCommand($command);
		}
	}

	protected function initializeCommandHandlers() {
		if ($this->commandHandlers === NULL) {
			$classNames = $this->reflectionService->getAllImplementationClassNamesForInterface(CommandHandlerInterface::class);
			$this->commandHandlers = array();
			foreach ($classNames as $className) {
				$this->commandHandlers[] = $this->objectManager->get($className);
			}
		}
	}
}
