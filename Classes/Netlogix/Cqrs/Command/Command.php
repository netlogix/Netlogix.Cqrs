<?php
namespace Netlogix\Cqrs\Command;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;


/**
 * Synchronous command
 */
abstract class Command extends AbstractCommand implements SynchronousCommandInterface {

}
