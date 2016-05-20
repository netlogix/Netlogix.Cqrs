<?php
namespace Netlogix\Cqrs\Tests\Behavior;

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Flowpack\Behat\Tests\Behat\FlowContext;
use Netlogix\Cqrs\Command\CommandBus;
use Netlogix\Cqrs\Command\CommandInterface;
use TYPO3\Flow\Resource\ResourceManager;
use TYPO3\Flow\Utility\Arrays;
use PHPUnit_Framework_Assert as Assert;
use TYPO3\Flow\Property\PropertyMapper;
use TYPO3\Flow\Property\PropertyMappingConfiguration;
use TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter;

require_once(__DIR__ . '/../../../../../Flowpack.Behat/Tests/Behat/FlowContext.php');

/**
 * Features context
 */
class CqrsContext extends FlowContext {

	/**
	 * @var string
	 */
	protected $package;

	/**
	 * @var CommandInterface
	 */
	protected $command;

	/**
	 * @var CommandBus
	 */
	protected $commandBus;

	/**
	 * Initializes the context
	 *
	 * @param array $parameters Context parameters (configured through behat.yml)
	 */
	public function __construct(array $parameters) {
		parent::__construct($parameters);

		$this->commandBus = $this->objectManager->get(CommandBus::class);
	}

	/**
	 * @BeforeScenario
	 * @param ScenarioEvent $event
	 */
	public function beforeScenario(ScenarioEvent $event) {
		$featureFile = $event->getScenario()->getFile();
		if (preg_match('~Packages/[^/]+/([^/]+)/Tests/Behavior/~', $featureFile, $matches) === 1) {
			$this->package = $matches[1];
		}
	}

	/**
	 * @Given /^I have a "([^"]*)" command with parameters$/
	 * @var string $commandName
	 * @var TableNode $parameters
	 * @throws \Exception
	 */
	public function iHaveACommandWithParameters($commandName, TableNode $parameters)
	{
		$propertyMappingConfiguration = new PropertyMappingConfiguration();
		$propertyMappingConfiguration->allowAllProperties();
		$propertyMappingConfiguration->setTypeConverterOption(PersistentObjectConverter::class, PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, true);
		$propertyMapper = $this->objectManager->get(PropertyMapper::class);

		$commandClass = $this->resolveClassName($commandName, 'Domain\\Command\\', 'Command');
		$this->command = $propertyMapper->convert($parameters->getRowsHash(), $commandClass, $propertyMappingConfiguration);
	}

	/**
	 * @Given /^I have a "([^"]*)" with values$/
	 * @param string $class
	 * @param TableNode $values
	 */
	public function iHaveAEntityWithValues($class, TableNode $values) {
		$propertyMappingConfiguration = new \TYPO3\Flow\Property\PropertyMappingConfiguration();
		$propertyMappingConfiguration->allowAllProperties();
		$propertyMappingConfiguration->forProperty('*')->allowAllProperties();
		$propertyMappingConfiguration->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, TRUE);
		/** @var \TYPO3\Flow\Property\PropertyMapper $propertyMapper */
		$propertyMapper = $this->objectManager->get('TYPO3\Flow\Property\PropertyMapper');
		$row = $values->getRowsHash();
		$identifier = '';
		if (isset($row['persistence_object_identifier'])) {
			$identifier = $row['persistence_object_identifier'];
			unset($row['persistence_object_identifier']);
		}
		// Handle dot notation
		foreach ($row as $key => $value) {
			if (strpos($key, '.') !== FALSE) {
				$keyParts = explode('.', $key);
				$currentPosition = &$row;
				foreach ($keyParts as $keyPart) {
					if (!array_key_exists($keyPart, $currentPosition)) {
						$currentPosition[$keyPart] = array();
					}
					$currentPosition = &$currentPosition[$keyPart];
				}
				$currentPosition = $value;
				unset($row[$key]);
			}
		}

		$entityClass = $this->resolveClassName($class, 'Domain\\Model\\');

		$entity = $propertyMapper->convert($row, $entityClass, $propertyMappingConfiguration);
		if ($propertyMapper->getMessages()->hasErrors()) {
			throw new \Exception('Error while mapping entity: ' . print_r($propertyMapper->getMessages(), TRUE));
		}
		if ($identifier) {
			\TYPO3\Flow\Reflection\ObjectAccess::setProperty($entity, 'Persistence_Object_Identifier', $identifier, TRUE);
		}
		$this->objectManager->get(\TYPO3\Flow\Persistence\PersistenceManagerInterface::class)->add($entity);
		$this->persistAll();
	}

	/**
	 * @Given /^I have a resource "([^"]*)" with content "([^"]*)"$/
	 * @param string $identifier
	 * @param string $content
	 */
	public function iHaveAResourceWithContent($identifier, $content) {
		$resourceManager = $this->objectManager->get(ResourceManager::class);
		$resourceManager->importResourceFromContent($content, $identifier, ResourceManager::DEFAULT_PERSISTENT_COLLECTION_NAME, $identifier);
		$this->persistAll();
	}


	/**
	 * @When /^I execute the command$/
	 */
	public function iExecuteTheCommand() {
		$this->commandBus->delegate($this->command);
		$this->persistAll();
	}

	/**
	 * @Then /^the database contains a "([^"]*)" with values$/
	 * @param string $modelName
	 * @param TableNode $values
	 * @throws \Exception
	 */
	public function theDatabaseContainsAWithValues($modelName, TableNode $values) {
		$modelClass = $this->resolveClassName($modelName, 'Domain\\Model\\');

		$query = $this->buildQueryForEntity($modelClass, $values->getRowsHash());

		$result = $query->getResult();

		if (count($result) !== 1) {
			throw new \Exception('Query returned ' . count($result) . ' results');
		}
	}

	/**
	 * @Then /^the database contains no "([^"]*)" with values$/
	 * @param string $modelName
	 * @param TableNode $values
	 * @throws \Exception
	 */
	public function theDatabaseContainsNoWithValues($modelName, TableNode $values) {
		$modelClass = $this->resolveClassName($modelName, 'Domain\\Model\\');

		$query = $this->buildQueryForEntity($modelClass, $values->getRowsHash());

		$result = $query->getResult();

		if (count($result) !== 0) {
			throw new \Exception('Query returned ' . count($result) . ' results');
		}
	}

	/**
	 * @param string $className
	 * @param string $prefix
	 * @param string $suffix
	 * @return string
	 */
	protected function resolveClassName($className, $prefix = '', $suffix = '') {
		foreach ([$className, str_replace('.', '\\', $this->package) . '\\' . $prefix . $className . $suffix] as $possibleClassName) {
			if (class_exists($possibleClassName)) {
				return $possibleClassName;
			}
		}
		throw new \Exception('Could not find class "' . $prefix . $className . $suffix . '"');
	}

	/**
	 * Build a query to find a entity based on values
	 *
	 * @param string $modelClass
	 * @param array $values
	 * @return \Doctrine\ORM\Query
	 * @throws \Doctrine\Common\Persistence\Mapping\MappingException
	 */
	protected function buildQueryForEntity($modelClass, array $values) {
		/** @var EntityManager $entityManager */
		$entityManager = $this->objectManager->get('Doctrine\Common\Persistence\ObjectManager');
		$queryBuilder = $entityManager->createQueryBuilder();
		$queryBuilder
			->select('m')
			->from($modelClass, 'm');

		$modelClassMetaData = $entityManager->getMetadataFactory()->getMetadataFor($modelClass);

		$existingJoins = [];
		foreach ($values as $key => $value) {
			$parameterName = $key;
			if (strpos($parameterName, '.') !== false) {
				$parameterName = str_replace('.', '_', $parameterName);
				list($key, $childKey) = explode('.', $key);
				if (!isset($existingJoins[$key])) {
					$existingJoins[$key] = $key;
					$queryBuilder->join('m.' . $key, $key);
				}
				$queryBuilder->andWhere($queryBuilder->expr()->eq($key . '.' . $childKey, ':' . $parameterName));
			} elseif ($modelClassMetaData->hasAssociation($key)) {
				if (!isset($existingJoins[$key])) {
					$existingJoins[$key] = $key;
					$queryBuilder->join('m.' . $key, $key);
				}
				$queryBuilder->andWhere($queryBuilder->expr()->eq($key, ':' . $parameterName));
			} else {
				$queryBuilder->andWhere($queryBuilder->expr()->eq('m.' . $key, ':' . $parameterName));
			}
			$queryBuilder->setParameter($parameterName, $value);
		}

		return $queryBuilder->getQuery();
	}
}
