<?php
namespace Netlogix\Cqrs\Tests\Unit\Command;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Netlogix\Cqrs\Command\Command;

/**
 * Testcase for command
 */
class CommandTest extends \TYPO3\Flow\Tests\UnitTestCase {

	public function testGeneratingCommandId() {
		/** @var Command|\PHPUnit_Framework_MockObject_MockObject $command */
		$command = $this->getMockBuilder(Command::class)->enableOriginalConstructor()->getMockForAbstractClass();
		$this->assertRegExp('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', $command->getCommandId());
	}
}
