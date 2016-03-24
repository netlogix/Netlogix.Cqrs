<?php
namespace Netlogix\Cqrs\Tests\Unit\Command;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Netlogix\Cqrs\Command\Command;
use Netlogix\Cqrs\Command\CommandBus;
use Netlogix\Cqrs\Command\CommandHandlerInterface;
use Netlogix\Cqrs\Command\CommandInterface;
use Netlogix\Cqrs\Log\CommandLogger;

class CommandBusTest extends \TYPO3\Flow\Tests\UnitTestCase {

	public function testCommandIsDelegatedToCommandHandler() {
		/** @var CommandInterface|\PHPUnit_Framework_MockObject_MockObject $mockCommand */
		$mockCommand = $this->getMockBuilder(CommandInterface::class)->getMockForAbstractClass();

		$mockCommandHandler = $this->getMockBuilder(CommandHandlerInterface::class)->getMockForAbstractClass();
		$mockCommandHandler->expects($this->once())->method('handle')->with($mockCommand);

		$commandBus = new CommandBus();
		$this->inject($commandBus, 'commandHandler', $mockCommandHandler);

		$commandBus->delegate($mockCommand);
	}
	
	public function testCommandIsLogged() {
		/** @var CommandInterface|\PHPUnit_Framework_MockObject_MockObject $mockCommand */
		$mockCommand = $this->getMockBuilder(Command::class)->getMockForAbstractClass();

		$mockCommandHandler = $this->getMockBuilder(CommandHandlerInterface::class)->getMockForAbstractClass();
		
		$mockCommandLogger = $this->getMockBuilder(CommandLogger::class)->getMock();
		$mockCommandLogger->expects($this->once())->method('logCommand')->with($mockCommand);

		$commandBus = new CommandBus();
		$this->inject($commandBus, 'commandHandler', $mockCommandHandler);
		$this->inject($commandBus, 'commandLogger', $mockCommandLogger);

		$commandBus->delegate($mockCommand);
		
	}
}
