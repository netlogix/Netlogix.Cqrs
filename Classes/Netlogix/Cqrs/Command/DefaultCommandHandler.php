<?php
namespace Netlogix\Cqrs\Command;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

/**
 * Handles commands and executes them directly
 */
class DefaultCommandHandler implements CommandHandlerInterface
{

    /**
     * Check whether a command handler can handle a given command
     *
     * @param CommandInterface $command
     * @return boolean
     */
    public function canHandle(CommandInterface $command)
    {
        return $command instanceof SynchronousCommandInterface;
    }

    /**
     * Execute the given command
     *
     * @param CommandInterface $command
     */
    public function handle(CommandInterface $command)
    {
        if (!($command instanceof SynchronousCommandInterface)) {
            throw new \InvalidArgumentException('$command must implement SynchronousCommandInterface to be handled',
                1465484134);
        }
        $command->execute();
    }
}
