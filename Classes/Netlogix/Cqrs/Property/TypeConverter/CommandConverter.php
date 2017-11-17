<?php
namespace Netlogix\Cqrs\Property\TypeConverter;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Netlogix\Cqrs\Log\CommandLogEntry;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Property\PropertyMappingConfigurationInterface;
use Neos\Flow\Property\TypeConverter\Error\TargetNotFoundError;
use Neos\Flow\Property\TypeConverter\PersistentObjectConverter;

class CommandConverter extends PersistentObjectConverter {

	/**
	 * @var string
	 */
	protected $targetType = 'Netlogix\\Cqrs\\Command\\AbstractCommand';

	/**
	 * Only convert non-persistent types
	 *
	 * @param mixed $source
	 * @param string $targetType
	 * @return boolean
	 */
	public function canConvertFrom($source, $targetType) {
		if (!is_subclass_of($targetType, $this->targetType)) {
			return FALSE;
		}
		if (is_array($source) && !isset($source['__identity'])) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * @inheritdoc
	 */
	public function convertFrom($source, $targetType, array $convertedChildProperties = array(), PropertyMappingConfigurationInterface $configuration = NULL) {
		/** @var CommandLogEntry $commandLogger */
		$commandLogger = parent::convertFrom($source, CommandLogEntry::class, $convertedChildProperties, $configuration);
		if ($commandLogger instanceof TargetNotFoundError) {
			return $commandLogger;
		}
		return $commandLogger->getCommand();
	}

}
