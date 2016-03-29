<?php
namespace Netlogix\Cqrs\Tests\Functional\Fixtures;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Netlogix\Cqrs\Command\Command;

class EntityContainingTestCommand extends Command {

	/**
	 * @var TestEntity
	 */
	protected $entity;

	public function __construct($commandId, TestEntity $entity) {
		$this->commandId = $commandId;
		$this->entity = $entity;
	}

	public function execute() {
		// Do nothing
	}
	
	public function getEntity() {
		return $this->entity;
	}
}