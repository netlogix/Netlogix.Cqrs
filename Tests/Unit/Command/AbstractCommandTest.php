<?php
namespace Netlogix\Cqrs\Tests\Unit\Command;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Netlogix\Cqrs\Command\AbstractCommand;
use Netlogix\Cqrs\Command\CommandInterface;
use Netlogix\Cqrs\Command\CommandStatusObserverInterface;

/**
 * Testcase for command
 */
class AbstractCommandTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var AbstractCommand|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $command;

	public function setUp() {
		$this->command = $this->getMockBuilder(AbstractCommand::class)->enableOriginalConstructor()->getMockForAbstractClass();
	}

	public function testGeneratingCommandId() {
		$this->assertRegExp('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', $this->command->getCommandId());
	}

	public function testGettingDefaultStatus() {
		$this->assertEquals(CommandInterface::STATUS_PENDING, $this->command->getStatus());
	}

	public function testUpdatingStatus() {
		$reflectionMethod = new \ReflectionMethod($this->command, 'updateStatus');
		$reflectionMethod->setAccessible(TRUE);
		$reflectionMethod->invoke($this->command, CommandInterface::STATUS_SUCCESS);
		$this->assertEquals(CommandInterface::STATUS_SUCCESS, $this->command->getStatus());
	}

	/**
	 * @var int $status
	 * @expectedException \InvalidArgumentException
	 * @dataProvider invalidStatusValues
	 */
	public function testUpdatingStatusWithInvalidValue($status) {
		$reflectionMethod = new \ReflectionMethod($this->command, 'updateStatus');
		$reflectionMethod->setAccessible(TRUE);
		$reflectionMethod->invoke($this->command, $status);
	}

	public function testStatusObserverIsCalledWhenStatusIsUpdated() {
		/** @var CommandStatusObserverInterface|\PHPUnit_Framework_MockObject_MockObject $mockObserver */
		$mockObserver = $this->getMockBuilder(CommandStatusObserverInterface::class)->getMockForAbstractClass();
		$mockObserver->expects($this->once())->method('update')->with($this->command, CommandInterface::STATUS_PENDING);
		$this->command->attachStatusObserver($mockObserver);

		$reflectionMethod = new \ReflectionMethod($this->command, 'updateStatus');
		$reflectionMethod->setAccessible(TRUE);
		$reflectionMethod->invoke($this->command, CommandInterface::STATUS_SUCCESS);
	}

	/**
	 * @return array
	 */
	public function invalidStatusValues() {
		return [
			[0],
			[-1],
			[5],
		];
	}
}
