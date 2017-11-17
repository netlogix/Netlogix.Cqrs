<?php
namespace Netlogix\Cqrs\Validation\Validator;

use Netlogix\Cqrs\Command\AbstractCommand;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Validation\Exception\InvalidValidationOptionsException;
use Neos\Flow\Validation\Validator\AbstractValidator;

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
            /** @var \Neos\Flow\Validation\Error $error */
            foreach ($validationResult as $error) {
                $this->result->forProperty($key)->addError($error);
            }
        }
    }
}