<?php
namespace Netlogix\Cqrs\Command;

/*
 * This file is part of the Netlogix. package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Reflection\ReflectionService;
use Netlogix\Cqrs\Log\CommandLogger;

/**
 * The command bus accepts commands and delegates execution of given commands
 */
class CommandBus
{

    /**
     * @var CommandHandlerInterface[]
     */
    protected $commandHandlers;

    /**
     * @var CommandLogger
     * @Flow\Inject
     */
    protected $commandLogger;

    /**
     * @var ReflectionService
     * @Flow\Inject
     */
    protected $reflectionService;

    /**
     * @var ObjectManagerInterface
     * @Flow\Inject
     */
    protected $objectManager;

    /**
     * @param CommandInterface $command
     * @throws \Exception
     */
    public function delegate(CommandInterface $command)
    {
        $this->initializeCommandHandlers();
        try {
            $this->logCommand($command);
            foreach ($this->commandHandlers as $commandHandler) {
                if ($commandHandler->canHandle($command)) {
                    $commandHandler->handle($command);
                }
            }
            $this->logCommand($command);
        } catch (\Exception $e) {
            $this->logCommand($command, $e);
            throw $e;
        }
    }

    /**
     * @param CommandInterface $command
     * @param \Exception $exception
     */
    protected function logCommand(CommandInterface $command, \Exception $exception = null)
    {
        if ($command instanceof AbstractCommand) {
            $this->commandLogger->logCommand($command, $exception);
        }
    }

    protected function initializeCommandHandlers()
    {
        if ($this->commandHandlers === null) {
            $classNames = $this->reflectionService->getAllImplementationClassNamesForInterface(CommandHandlerInterface::class);
            $this->commandHandlers = array();
            foreach ($classNames as $className) {
                $this->commandHandlers[] = $this->objectManager->get($className);
            }
        }
    }
}
