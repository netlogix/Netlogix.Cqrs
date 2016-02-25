<?php
namespace Netlogix\Cqrs\Command;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

/**
 * A command handler accpets commands and handles their execution
 */
interface CommandHandlerInterface {

	/**
	 * Handle a given command
	 *
	 * @param CommandInterface $command
	 * @return void
	 */
	public function handle(CommandInterface $command);
}