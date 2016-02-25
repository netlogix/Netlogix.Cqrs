<?php
namespace Netlogix\Cqrs\Command;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

/**
 * Interface CommandInterface
 */
interface CommandInterface {

	/**
	 * Executes the command.
	 *
	 * @return void
	 */
	public function execute();
}