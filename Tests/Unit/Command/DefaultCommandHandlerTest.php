<?php
namespace Netlogix\Cqrs\Tests\Unit\Command;

/*
 * This file is part of the Netlogix. package.
 */

use Netlogix\Cqrs\Command\CommandInterface;
use Netlogix\Cqrs\Command\DefaultCommandHandler;

class DefaultCommandHandlerTest extends \PHPUnit_Framework_TestCase {

	public function testAHandledCommandIsExecuted() {
		$mockCommand = $this->getMockBuilder(CommandInterface::class)->getMockForAbstractClass();
		$mockCommand->expects($this->once())->method('execute');

		$defaultCommandHandler = new DefaultCommandHandler();
		$defaultCommandHandler->handle($mockCommand);
	}
}
