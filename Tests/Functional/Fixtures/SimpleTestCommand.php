<?php
namespace Netlogix\Cqrs\Tests\Functional\Fixtures;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Netlogix\Cqrs\Command\Command;

class SimpleTestCommand extends Command {
	
	public function __construct($commandId) {
		$this->commandId = $commandId;
	}

	public function execute() {
		// Do nothing
	}
}