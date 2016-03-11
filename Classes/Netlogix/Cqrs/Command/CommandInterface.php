<?php
namespace Netlogix\Cqrs\Command;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

/**
 * Interface CommandInterface
 */
interface CommandInterface {

	const STATUS_PENDING = 1;
	const STATUS_REJECTED = 2;
	const STATUS_SUCCESS = 3;
	const STATUS_FAILED = 4;

	/**
	 * Executes the command.
	 *
	 * @return void
	 */
	public function execute();

	/**
	 * Get the current status of the command. This will return one of the defined STATUS_* constants.
	 *
	 * @return int
	 */
	public function getStatus();

	/**
	 * Registers an observer which should be notified one every status change.
	 *
	 * @param CommandStatusObserverInterface $observer
	 * @return void
	 */
	public function attachStatusObserver(CommandStatusObserverInterface $observer);

}