<?php
namespace Netlogix\Cqrs\Log;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Netlogix\Cqrs\Command\AbstractCommand;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class CommandLogEntry {

	/**
	 * @var string
	 * @ORM\Id
	 * @ORM\Column(type="guid")
	 */
	protected $commandId;

	/**
	 * @var string
	 */
	protected $commandType;

	/**
	 * @var \DateTime
	 */
	protected $executionDateAndTime;

	/**
	 * @var integer
	 */
	protected $status;

	/**
	 * The command as it was executed. Doctrine saves the command as a serialized string. Using Flows persistence magic
	 * this ensures that entities that belong to a command are correctly stored and retrieved from the database.
	 *
	 * @var AbstractCommand
	 * @ORM\Column(type="objectblob")
	 */
	protected $command;

	/**
	 * @var ExceptionData
	 * @ORM\Column(type="objectblob",nullable=true)
	 */
	protected $exception = null;

	/**
	 * CommandLogEntry constructor.
	 *
	 * @param AbstractCommand $command
	 */
	public function __construct(AbstractCommand $command) {
		$this->commandId = $command->getCommandId();
		$this->commandType = get_class($command);
		$this->command = $command;
		$this->executionDateAndTime = new \DateTime();
		$this->status = $command->getStatus();
	}

	/**
	 * Get the command id
	 *
	 * @return string
	 */
	public function getCommandId() {
		return $this->commandId;
	}

	/**
	 * Get the type of the command
	 *
	 * @return string
	 */
	public function getCommandType() {
		return $this->commandType;
	}

	/**
	 * Get the command itself
	 *
	 * @return AbstractCommand
	 */
	public function getCommand() {
		return $this->command;
	}

	/**
	 * @return \DateTime
	 */
	public function getExecutionDateAndTime() {
		return $this->executionDateAndTime;
	}

	/**
	 * @return integer
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @return ExceptionData
	 */
	public function getException() {
		return $this->exception;
	}

	/**
	 * @param ExceptionData $exception
	 */
	public function setException($exception) {
		$this->exception = $exception;
	}

}