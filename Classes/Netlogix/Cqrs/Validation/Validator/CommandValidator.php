<?php
namespace Netlogix\Cqrs\Validation\Validator;

use Netlogix\Cqrs\Command\AbstractCommand;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException;
use TYPO3\Flow\Validation\Validator\AbstractValidator;

/**
 * @Flow\Scope("singleton")
 */
class CommandValidator extends AbstractValidator
{

    /**
     * @param AbstractCommand $value
     * @throws InvalidValidationOptionsException
     */
    protected function isValid($value)
    {
        if (!$value instanceof AbstractCommand) {
            throw new InvalidValidationOptionsException('The value supplied for the CommandValidator must be of type Command.',
                1459251080);
        }

        foreach ($value->getValidationResult()->getFlattenedErrors() as $key => $validationResult) {
            /** @var \TYPO3\Flow\Validation\Error $error */
            foreach ($validationResult as $error) {
                $this->result->forProperty($key)->addError($error);
            }
        }
    }
}