<?php
namespace Netlogix\Cqrs\Command;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Neos\Error\Messages\Result;
use Neos\Flow\Utility\Algorithms;

/**
 * Abstract command
 */
abstract class AbstractCommand implements CommandInterface{
	/**
	 * @var string
	 */
	protected $Persistence_Object_Identifier;

	/**
	 * @var string
	 */
	protected $commandId;

	/**
	 * @var int
	 * @see Command::updateStatus
	 */
	private $status = CommandInterface::STATUS_PENDING;

	/**
	 * @var CommandStatusObserverInterface[]
	 */
	private $statusUpdateObservers = [];

	/**
	 * Creates a new command and assigns an automatic id to it
	 */
	public function __construct() {
		$this->commandId = Algorithms::generateUUID();
		$this->Persistence_Object_Identifier = $this->commandId;
	}

	/**
	 * @return string
	 */
	public function getCommandId() {
		return $this->commandId;
	}

	/**
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @param CommandStatusObserverInterface $observer
	 */
	public function attachStatusObserver(CommandStatusObserverInterface $observer) {
		$this->statusUpdateObservers[] = $observer;
	}

	/**
	 * @return Result
	 */
	public function getValidationResult() {
		return new Result();
	}

	/**
	 * Updates the status of this command. This method should be called by execute() when the status of the command
	 * changes.
	 *
	 * @param int $newStatus
	 */
	protected function updateStatus($newStatus) {
		if ($newStatus < CommandInterface::STATUS_PENDING || $newStatus > CommandInterface::STATUS_FAILED) {
			throw new \InvalidArgumentException('Command status updated to invalid value ' . $newStatus . '. $newStatus must be one of CommandInterface::STATUS_* constants', 1457113082);
		}

		$oldStatus = $this->status;
		$this->status = $newStatus;

		foreach ($this->statusUpdateObservers as $observer) {
			$observer->update($this, $oldStatus);
		}
	}
}
