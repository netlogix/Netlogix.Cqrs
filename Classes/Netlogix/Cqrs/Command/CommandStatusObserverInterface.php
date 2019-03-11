<?php
namespace Netlogix\Cqrs\Command;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

/**
 * Interface CommandStatusObserverInterface
 */
interface CommandStatusObserverInterface
{

    /**
     * This method is called on status update.
     *
     * @param CommandInterface $command
     * @param int $oldStatus
     * @return void
     */
    public function update(CommandInterface $command, $oldStatus);
}