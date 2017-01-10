<?php
namespace Netlogix\Cqrs\Log;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use TYPO3\Flow\Persistence\Repository;
use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class CommandLogEntryRepository extends Repository {

	/**
	 * @var string
	 */
	const ENTITY_CLASSNAME = CommandLogEntry::class;

}