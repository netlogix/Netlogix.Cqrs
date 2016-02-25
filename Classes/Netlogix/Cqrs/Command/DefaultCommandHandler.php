<?php
namespace Netlogix\Cqrs\Command;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

/**
 * Handles commands and executes them directly
 */
class DefaultCommandHandler implements CommandHandlerInterface{

	/**
	 * Execute the given command
	 *
	 * @param CommandInterface $command
	 */
	public function handle(CommandInterface $command) {
		$command->execute();
	}
}