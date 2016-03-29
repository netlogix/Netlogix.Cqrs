<?php
namespace Netlogix\Cqrs\Tests\Unit\Log;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Netlogix\Cqrs\Command\Command;
use Netlogix\Cqrs\Log\CommandLogger;
use Netlogix\Cqrs\Log\CommandLogEntryRepository;

class CommandLoggerTest extends \TYPO3\Flow\Tests\UnitTestCase {

	public function testCommandsAreLogged() {
		/** @var Command|\PHPUnit_Framework_MockObject_MockObject $mockCommand */
		$mockCommand = $this->getMockBuilder(Command::class)->getMockForAbstractClass();
		$mockRepository = $this->getMockBuilder(CommandLogEntryRepository::class)->disableOriginalConstructor()->getMock();
		$mockRepository->expects($this->once())->method('add');

		$commandLogger = new CommandLogger();
		$this->inject($commandLogger, 'commandLogEntryRepository', $mockRepository);
		$commandLogger->logCommand($mockCommand);
	}
}
