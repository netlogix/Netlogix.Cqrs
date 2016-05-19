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
		$propertyMappingConfiguration->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, TRUE);
		/** @var \TYPO3\Flow\Property\PropertyMapper $propertyMapper */
		$propertyMapper = $this->objectManager->get('TYPO3\Flow\Property\PropertyMapper');
		$row = $values->getRowsHash();
		$identifier = '';
		if (isset($row['persistence_object_identifier'])) {
			$identifier = $row['persistence_object_identifier'];
			unset($row['persistence_object_identifier']);
		}

		$entityClass = $this->resolveClassName($class, 'Domain\\Model\\');

		$entity = $propertyMapper->convert($row, $entityClass, $propertyMappingConfiguration);
		if ($propertyMapper->getMessages()->hasErrors()) {
			throw new \Exception('Error while mapping entity: ' . print_r($propertyMapper->getMessages(), TRUE));
		}
		if ($identifier) {
			\TYPO3\Flow\Reflection\ObjectAccess::setProperty($entity, 'Persistence_Object_Identifier', $identifier, TRUE);
		}
		$this->objectManager->get($this->resolveClassName($class, 'Domain\\Repository\\', 'Repository'))->add($entity);
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

		/** @var EntityManager $entityManager */
		$entityManager = $this->objectManager->get('Doctrine\Common\Persistence\ObjectManager');
		$queryBuilder = $entityManager->createQueryBuilder();
		$queryBuilder
			->select('m')
			->from($modelClass, 'm')
			->setParameters($values->getRowsHash());

		foreach ($values->getRowsHash() as $key => $value) {
			$queryBuilder->andWhere($queryBuilder->expr()->eq('m.' . $key, ':' . $key));
		}

		$result = $queryBuilder->getQuery()->getResult();

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

		/** @var EntityManager $entityManager */
		$entityManager = $this->objectManager->get('Doctrine\Common\Persistence\ObjectManager');
		$queryBuilder = $entityManager->createQueryBuilder();
		$queryBuilder
			->select('m')
			->from($modelClass, 'm')
			->setParameters($values->getRowsHash());

		foreach ($values->getRowsHash() as $key => $value) {
			$queryBuilder->andWhere($queryBuilder->expr()->eq('m.' . $key, ':' . $key));
		}

		$result = $queryBuilder->getQuery()->getResult();

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
}
