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
	 * @throws \Exception
	 */
	public function delegate(CommandInterface $command) {
		$this->initializeCommandHandlers();
		try {
			foreach ($this->commandHandlers as $commandHandler) {
				if ($commandHandler->canHandle($command)) {
					$commandHandler->handle($command);
				}
			}
		} catch (\Exception $e) {
			$this->logCommand($command, $e);
			throw $e;
		}
		$this->logCommand($command);
	}

	/**
	 * @param CommandInterface $command
	 * @param \Exception $exception
	 */
	protected function logCommand(CommandInterface $command, \Exception $exception = null) {
		if ($command instanceof Command) {
			$this->commandLogger->logCommand($command, $exception);
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
