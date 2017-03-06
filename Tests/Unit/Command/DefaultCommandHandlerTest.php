<?php
namespace Netlogix\Cqrs\Tests\Unit\Command;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Netlogix\Cqrs\Command\Command;
use Netlogix\Cqrs\Command\CommandInterface;
use Netlogix\Cqrs\Command\DefaultCommandHandler;
use Netlogix\Cqrs\Command\SynchronousCommandInterface;

class DefaultCommandHandlerTest extends \TYPO3\Flow\Tests\UnitTestCase {

	public function testAHandledCommandIsExecuted() {
		$mockCommand = $this->getMockBuilder(SynchronousCommandInterface::class)->getMockForAbstractClass();
		$mockCommand->expects($this->once())->method('execute');

		$defaultCommandHandler = new DefaultCommandHandler();
		$defaultCommandHandler->handle($mockCommand);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testOnlySynchronousCommandsAreHandled() {
		$mockCommand = $this->getMockBuilder(CommandInterface::class)->getMockForAbstractClass();

		$defaultCommandHandler = new DefaultCommandHandler();
		$defaultCommandHandler->handle($mockCommand);
	}

	public function testCanHandleSynchronousCommands() {
		$mockCommand = $this->getMockBuilder(SynchronousCommandInterface::class)->getMockForAbstractClass();

		$defaultCommandHandler = new DefaultCommandHandler();
		$this->assertTrue($defaultCommandHandler->canHandle($mockCommand));
	}

	public function testCanNotHandleNormalCommands() {
		$mockCommand = $this->getMockBuilder(CommandInterface::class)->getMockForAbstractClass();

		$defaultCommandHandler = new DefaultCommandHandler();
		$this->assertFalse($defaultCommandHandler->canHandle($mockCommand));
	}

	public function testCanHandleBasicCommands() {
		$mockCommand = $this->getMockBuilder(Command::class)->getMockForAbstractClass();

		$defaultCommandHandler = new DefaultCommandHandler();
		$this->assertTrue($defaultCommandHandler->canHandle($mockCommand));
	}
}
