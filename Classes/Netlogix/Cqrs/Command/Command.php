<?php
namespace Netlogix\Cqrs\Command;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Utility\Algorithms;

/**
 * @Flow\Entity
 */
abstract class Command implements CommandInterface{

	/**
	 * @var string
	 * @Flow\Identity
	 * @ORM\Column(length=36)
	 */
	protected $commandId;

	/**
	 * Creates a new command and assigns an automatic id to it
	 */
	public function __construct() {
		$this->commandId = Algorithms::generateUUID();
	}

	/**
	 * @return string
	 */
	public function getCommandId() {
		return $this->commandId;
	}
}