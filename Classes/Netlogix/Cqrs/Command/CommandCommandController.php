<?php
namespace Netlogix\Cqrs\Command;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

/**
 * The Command Command Controller
 *
 * @Flow\Scope("singleton")
 */
class CommandCommandController extends CommandController {

	/**
	 * @var CommandBus
	 * @Flow\Inject
	 */
	protected $commandBus;

	protected function mapRequestArgumentsToControllerArguments() {
		$commandClass = $this->request->getArgument('command');
		$commandArgument = $this->arguments->getArgument('command');
		$commandArgument->setDataType($commandClass);
		$commandArgument->getPropertyMappingConfiguration()->allowAllProperties();
		$commandArguments = array();
		foreach ($this->request->getExceedingArguments() as $exceedingArgument) {
			list($name, $value) = explode(':', $exceedingArgument, 2);
			$commandArguments[$name] = $value;
		}
		$commandArgument->setValue($commandArguments);
	}

	/**
	 * @param AbstractCommand $command
	 */
	public function executeCommand(AbstractCommand $command) {
		$this->commandBus->delegate($command);
	}


}
