<?php
namespace Netlogix\Cqrs\Command;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

/**
 * A command handler accpets commands and handles their execution
 */
interface CommandHandlerInterface
{

    /**
     * Check whether a command handler can handle a given command
     *
     * @param CommandInterface $command
     * @return boolean
     */
    public function canHandle(CommandInterface $command);

    /**
     * Handle a given command
     *
     * @param CommandInterface $command
     * @return void
     */
    public function handle(CommandInterface $command);
}