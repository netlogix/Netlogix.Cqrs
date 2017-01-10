<?php

namespace Netlogix\Cqrs\Doctrine\Type;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Doctrine\DBAL\Platforms\AbstractPlatform;
use TYPO3\Flow\Annotations as Flow;

/**
 * A data type that encodes command objects for the database. In contrast to doctrines object type this uses
 * a BLOB column in the database. This has the advantage on Postgresql to support NULL bytes which happen to
 * occur in php serialized objects.
 *
 * @Flow\Proxy(false)
 */
class CommandType extends \Doctrine\DBAL\Types\ObjectType
{
	/**
	 * @var string
	 */
	const COMMANDOBJECT = 'commandobject';

	/**
	 * Gets the name of this type.
	 *
	 * @return string
	 */
	public function getName()
	{
		return self::COMMANDOBJECT;
	}

	/**
	 * Gets the SQL declaration snippet for a field of this type.
	 *
	 * @param array $fieldDeclaration
	 * @param AbstractPlatform $platform
	 * @return string
	 */
	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		return $platform->getBlobTypeDeclarationSQL($fieldDeclaration);
	}

	/**
	 * Gets the (preferred) binding type for values of this type that
	 * can be used when binding parameters to prepared statements.
	 *
	 * @return integer
	 */
	public function getBindingType()
	{
		return \PDO::PARAM_LOB;
	}

	/**
	 * Converts a value from its database representation to its PHP representation
	 * of this type.
	 *
	 * @param mixed $value The value to convert.
	 * @param AbstractPlatform $platform The currently used database platform.
	 * @return array The PHP representation of the value.
	 */
	public function convertToPHPValue($value, AbstractPlatform $platform)
	{

		switch ($platform->getName()) {
			case 'postgresql':
				$value = (is_resource($value)) ? stream_get_contents($value) : $value;
				$convertedValue = parent::convertToPHPValue(hex2bin($value), $platform);
				break;
			default:
				$convertedValue = parent::convertToPHPValue($value, $platform);
		}

		return $convertedValue;
	}

	/**
	 * Converts a value from its PHP representation to its database representation
	 * of this type.
	 *
	 * @param array $value The value to convert.
	 * @param AbstractPlatform $platform The currently used database platform.
	 * @return mixed The database representation of the value.
	 */
	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{

		switch ($platform->getName()) {
			case 'postgresql':
				return bin2hex(parent::convertToDatabaseValue($value, $platform));
			default:
				return parent::convertToDatabaseValue($value, $platform);
		}
	}

}
