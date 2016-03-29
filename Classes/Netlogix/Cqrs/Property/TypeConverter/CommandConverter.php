<?php
namespace Netlogix\Cqrs\Property\TypeConverter;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Netlogix\Cqrs\Log\CommandLogEntry;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Property\PropertyMappingConfigurationInterface;
use TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter;

class CommandConverter extends PersistentObjectConverter
{

    /**
     * @var string
     */
    protected $targetType = 'Netlogix\\Cqrs\\Command\\Command';

    /**
     * Only convert non-persistent types
     *
     * @param mixed $source
     * @param string $targetType
     * @return boolean
     */
    public function canConvertFrom($source, $targetType)
    {
        if (!is_subclass_of($targetType, $this->targetType)) {
            return false;
        }
        if (!isset($source['__identity'])) {
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = array(), PropertyMappingConfigurationInterface $configuration = null)
    {
        /** @var CommandLogEntry $commandLogger */
        $commandLogger = parent::convertFrom($source, CommandLogEntry::class, $convertedChildProperties, $configuration);
        return $commandLogger->getCommand();
    }

}