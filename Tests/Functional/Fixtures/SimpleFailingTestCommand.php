<?php
namespace Netlogix\Cqrs\Tests\Functional\Fixtures;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Netlogix\Cqrs\Command\Command;

class SimpleFailingTestCommand extends Command {

	public function __construct($commandId) {
		$this->commandId = $commandId;
	}

	public function execute() {
		throw new \InvalidArgumentException('Foo', 1488800470);
	}
}