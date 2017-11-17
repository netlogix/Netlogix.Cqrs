<?php
namespace Netlogix\Cqrs\Tests\Unit\Log;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Netlogix\Cqrs\Command\AbstractCommand;
use Netlogix\Cqrs\Log\CommandLogEntry;

class CommandLogEntryTest extends \Neos\Flow\Tests\UnitTestCase {

	public function testCommandIdIsCopiedToLogEntry() {
		/** @var AbstractCommand|\PHPUnit_Framework_MockObject_MockObject $mockCommand */
		$mockCommand = $this->getMockBuilder(AbstractCommand::class)->disableOriginalConstructor()->setMethods(['getCommandId'])->getMockForAbstractClass();
		$mockCommand->method('getCommandId')->willReturn('abcd1234-abcd-1234-ab12-1234abcd5678');

		$commandLogEntry = new CommandLogEntry($mockCommand);
		$this->assertEquals('abcd1234-abcd-1234-ab12-1234abcd5678', $commandLogEntry->getCommandId());
	}

	public function testCommandTypeIsStoredInLogEntry() {
		/** @var AbstractCommand|\PHPUnit_Framework_MockObject_MockObject $mockCommand */
		$mockCommand = $this->getMockBuilder(AbstractCommand::class)->disableOriginalConstructor()->setMockClassName('CommandTestMock')->getMock();

		$commandLogEntry = new CommandLogEntry($mockCommand);
		$this->assertEquals('CommandTestMock', $commandLogEntry->getCommandType());
	}

	public function testCommandIsStoredInLogEntry() {
		/** @var AbstractCommand|\PHPUnit_Framework_MockObject_MockObject $mockCommand */
		$mockCommand = $this->getMockBuilder(AbstractCommand::class)->disableOriginalConstructor()->getMockForAbstractClass();

		$commandLogEntry = new CommandLogEntry($mockCommand);
		$this->assertSame($mockCommand, $commandLogEntry->getCommand());
	}

	public function testCommandExecutionTimeIsStoredInLogEntry() {
		/** @var AbstractCommand|\PHPUnit_Framework_MockObject_MockObject $mockCommand */
		$mockCommand = $this->getMockBuilder(AbstractCommand::class)->disableOriginalConstructor()->getMockForAbstractClass();

		$beforeTime = new \DateTime();
		$commandLogEntry = new CommandLogEntry($mockCommand);
		$afterTime = new \DateTime();
		$this->assertGreaterThanOrEqual($beforeTime, $commandLogEntry->getExecutionDateAndTime());
		$this->assertLessThanOrEqual($afterTime, $commandLogEntry->getExecutionDateAndTime());
	}
}
