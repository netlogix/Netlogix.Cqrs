<?php
namespace Netlogix\Cqrs\Log;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Neos\Flow\Persistence\Repository;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class CommandLogEntryRepository extends Repository {

	/**
	 * @var string
	 */
	const ENTITY_CLASSNAME = CommandLogEntry::class;

}