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
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Reflection\ReflectionService;

class CommandBusTest extends \Neos\Flow\Tests\UnitTestCase {


	public function testCommandIsDelegatedToAllCommandHandlersWhichCanHandleTheCommand() {
		/** @var CommandInterface|\PHPUnit_Framework_MockObject_MockObject $mockCommand */
		$mockCommand = $this->getMockBuilder(CommandInterface::class)->getMockForAbstractClass();

		$mockCommandHandler1 = $this->getMockBuilder(CommandHandlerInterface::class)->getMockForAbstractClass();
		$mockCommandHandler1->expects($this->any())->method('canHandle')->willReturn(TRUE);
		$mockCommandHandler1->expects($this->once())->method('handle')->with($mockCommand);
		$mockCommandHandler2 = $this->getMockBuilder(CommandHandlerInterface::class)->getMockForAbstractClass();
		$mockCommandHandler2->expects($this->any())->method('canHandle')->willReturn(FALSE);
		$mockCommandHandler2->expects($this->never())->method('handle')->with($mockCommand);
		$mockCommandHandler3 = $this->getMockBuilder(CommandHandlerInterface::class)->getMockForAbstractClass();
		$mockCommandHandler3->expects($this->any())->method('canHandle')->willReturn(TRUE);
		$mockCommandHandler3->expects($this->once())->method('handle')->with($mockCommand);

		$commandBus = new CommandBus();
		$this->inject($commandBus, 'commandHandlers', array($mockCommandHandler1, $mockCommandHandler2, $mockCommandHandler3));

		$commandBus->delegate($mockCommand);
	}

	public function testCommandHandlersAreFound() {
		$mockCommandHandler1 = $this->getMockBuilder(CommandHandlerInterface::class)->getMockForAbstractClass();
		$mockCommandHandler1->expects($this->once())->method('canHandle')->willReturn(FALSE);
		$mockCommandHandler2 = $this->getMockBuilder(CommandHandlerInterface::class)->getMockForAbstractClass();
		$mockCommandHandler2->expects($this->once())->method('canHandle')->willReturn(FALSE);

		$mockReflectionService = $this->getMockBuilder(ReflectionService::class)->getMock();
		$mockReflectionService->method('getAllImplementationClassNamesForInterface')->willReturn(array('MockHandler1', 'MockHandler2'));
		$mockObjectManager = $this->getMockBuilder(ObjectManagerInterface::class)->getMockForAbstractClass();
		$mockObjectManager->method('get')->willReturnMap(array(array('MockHandler1', $mockCommandHandler1), array('MockHandler2', $mockCommandHandler2)));

		$commandBus = new CommandBus();
		$this->inject($commandBus, 'reflectionService', $mockReflectionService);
		$this->inject($commandBus, 'objectManager', $mockObjectManager);

		/** @var CommandInterface|\PHPUnit_Framework_MockObject_MockObject $mockCommand */
		$mockCommand = $this->getMockBuilder(CommandInterface::class)->getMockForAbstractClass();
		$commandBus->delegate($mockCommand);
	}
	
	public function testCommandIsLogged() {
		/** @var CommandInterface|\PHPUnit_Framework_MockObject_MockObject $mockCommand */
		$mockCommand = $this->getMockBuilder(Command::class)->getMockForAbstractClass();

		$mockCommandHandler = $this->getMockBuilder(CommandHandlerInterface::class)->getMockForAbstractClass();
		
		$mockCommandLogger = $this->getMockBuilder(CommandLogger::class)->getMock();
		$mockCommandLogger->expects($this->once())->method('logCommand')->with($mockCommand);

		$commandBus = new CommandBus();
		$this->inject($commandBus, 'commandHandlers', array($mockCommandHandler));
		$this->inject($commandBus, 'commandLogger', $mockCommandLogger);

		$commandBus->delegate($mockCommand);
		
	}
}
