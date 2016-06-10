<?php
namespace Netlogix\Cqrs\Tests\Functional\Log;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Doctrine\ORM\EntityNotFoundException;
use Netlogix\Cqrs\Log\CommandLogEntry;
use Netlogix\Cqrs\Log\CommandLogger;
use Netlogix\Cqrs\Tests\Functional\Fixtures\EntityContainingTestCommand;
use Netlogix\Cqrs\Tests\Functional\Fixtures\SimpleTestCommand;
use Netlogix\Cqrs\Tests\Functional\Fixtures\TestEntity;

class CommandLoggerTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	protected static $testablePersistenceEnabled = true;
	
	public function testCommandLogsAreStoredInDatabase() {
		$command = new SimpleTestCommand('9a395a80-fcfa-4029-bb3c-5bb23106a9ac');

		$commandLogger = $this->objectManager->get(CommandLogger::class);
		$commandLogger->logCommand($command);
		
		$this->persistenceManager->persistAll();
		
		$persistedLogEntry = $this->persistenceManager->getObjectByIdentifier('9a395a80-fcfa-4029-bb3c-5bb23106a9ac', CommandLogEntry::class);
		$this->assertNotNull($persistedLogEntry);
	}

	public function testSimpleCommandIsRestoredOnLogEntryRetrieval() {
		$command = new SimpleTestCommand('0774958b-c35f-40c3-a6f8-413a211d1456');

		$commandLogEntry = new CommandLogEntry($command);
		$this->persistenceManager->add($commandLogEntry);
		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		/** @var CommandLogEntry $persistedLogEntry */
		$persistedLogEntry = $this->persistenceManager->getObjectByIdentifier('0774958b-c35f-40c3-a6f8-413a211d1456', CommandLogEntry::class);
		$this->assertInstanceOf(SimpleTestCommand::class, $persistedLogEntry->getCommand());
	}

	public function testCommandContainingEntityIsRestoredOnLogEntryRetrieval() {
		$entity = new TestEntity();
		$entity->setFoo('bar');
		$this->persistenceManager->add($entity);
		$command = new EntityContainingTestCommand('fcb9eeb1-96d8-4ef6-8e35-0968b795470e', $entity);

		$commandLogEntry = new CommandLogEntry($command);
		$this->persistenceManager->add($commandLogEntry);
		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		/** @var CommandLogEntry $persistedLogEntry */
		$persistedLogEntry = $this->persistenceManager->getObjectByIdentifier('fcb9eeb1-96d8-4ef6-8e35-0968b795470e', CommandLogEntry::class);
		$this->assertInstanceOf(TestEntity::class, $persistedLogEntry->getCommand()->getEntity());
		$this->assertEquals($this->persistenceManager->getIdentifierByObject($entity), $this->persistenceManager->getIdentifierByObject($persistedLogEntry->getCommand()->getEntity()));
	}

	public function testCommandContainingDeletedEntityCanBeRestoredOnLogEntryRetrieval() {
		$entity = new TestEntity();
		$entity->setFoo('bar');
		$this->persistenceManager->add($entity);
		$command = new EntityContainingTestCommand('728b3930-2deb-44ef-92e9-8899dd02c775', $entity);

		$commandLogEntry = new CommandLogEntry($command);
		$this->persistenceManager->add($commandLogEntry);
		$this->persistenceManager->persistAll();
		$this->persistenceManager->remove($entity);
		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		/** @var CommandLogEntry $persistedLogEntry */
		$persistedLogEntry = $this->persistenceManager->getObjectByIdentifier('728b3930-2deb-44ef-92e9-8899dd02c775', CommandLogEntry::class);
		
		// FIXME find a cleaner way to check if the log entry can be loaded
		try {
			$persistedLogEntry->getCommand()->getEntity()->getFoo();
		} catch (EntityNotFoundException $e) {
			// We want the exception in the above line and not when loading the object, therefore @ExpectedException is not good enough
			$this->assertTrue(TRUE);
			return;
		}
		$this->fail('The property of the deleted entity could be accessed');



	}
}
