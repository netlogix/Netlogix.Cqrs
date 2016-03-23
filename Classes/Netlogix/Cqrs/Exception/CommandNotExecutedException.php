<?php
namespace Netlogix\Cqrs\Exception;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use TYPO3\Flow\Exception;

/**
 * An exception to indicate that a command was not yet executed and results are not available.
 */
class CommandNotExecutedException extends Exception {

}