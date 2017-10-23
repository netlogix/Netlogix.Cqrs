<?php
namespace Netlogix\Cqrs\Tests\Behavior;

use Behat\Behat\Event\ScenarioEvent;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManager;
use Flowpack\Behat\Tests\Behat\FlowContext;
use Netlogix\BehatCommons\ClassNameResolver;
use Netlogix\BehatCommons\ObjectFactory;
use Netlogix\Cqrs\Command\CommandBus;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Utility\ObjectAccess;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Flow\Property\PropertyMapper;

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
	 * @var array<CommandInterface>
	 */
	protected $commands = [];

	/**
	 * @var CommandBus
	 */
	protected $commandBus;

	/**
	 * @var ObjectFactory
	 */
	protected $objectFactory;

	/**
	 * @var ClassNameResolver
	 */
	protected $classNameResolver;

	/**
	 * Initializes the context
	 *
	 * @param array $parameters Context parameters (configured through behat.yml)
	 */
	public function __construct(array $parameters) {
		parent::__construct($parameters);

		$this->commandBus = $this->objectManager->get(CommandBus::class);
		$this->objectFactory = $this->objectManager->get(ObjectFactory::class);
		$this->classNameResolver = $this->objectManager->get(ClassNameResolver::class);
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
		$this->commands = [];
	}

	/**
	 * @AfterScenario
	 * @param ScenarioEvent $event
	 * @throws \Exception
	 */
	public function afterScenario(ScenarioEvent $event) {
		if ($this->commands) {
			throw new \Exception('There are still ' . count($this->commands) . ' not executed.');
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
		$commandClass = $this->resolveClassName($commandName, 'Domain\\Command\\', 'Command');
		$this->commands[] = $this->objectFactory->create($parameters->getRowsHash(), $commandClass, $this->objectManager->get(PropertyMapper::class));
	}

	/**
	 * @Given /^I have a "([^"]*)" with values$/
	 * @param string $class
	 * @param TableNode $values
	 * @throws \Exception
	 */
	public function iHaveAEntityWithValues($class, TableNode $values) {

		/** @var PropertyMapper $propertyMapper */
		$propertyMapper = $this->objectManager->get(PropertyMapper::class);

		$entityClass = $this->resolveClassName($class, 'Domain\\Model\\');

		$row = $values->getRowsHash();
		$identifier = '';
		if (isset($row['persistence_object_identifier'])) {
			$identifier = $row['persistence_object_identifier'];
			unset($row['persistence_object_identifier']);
		}

		$entity = $this->objectFactory->create($row, $entityClass, $propertyMapper);

		if ($propertyMapper->getMessages()->hasErrors()) {
			throw new \Exception('Error while mapping entity: ' . print_r($propertyMapper->getMessages(), TRUE));
		}
		if ($identifier) {
			ObjectAccess::setProperty($entity, 'Persistence_Object_Identifier', $identifier, TRUE);
		}

		$this->objectManager->get(PersistenceManagerInterface::class)->add($entity);
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
	 * @When /^I execute the commands$/
	 */
	public function iExecuteTheCommand() {
		while ($command = array_shift($this->commands)) {
			$this->commandBus->delegate($command);
		}
		$this->persistAll();
	}

	/**
	 * @When /^I execute the (first|next|last) command$/
	 */
	public function iExecuteTheFirstNextOrLastCommand() {
		$command = array_shift($this->commands);
		$this->commandBus->delegate($command);
		$this->persistAll();
	}

	/**
	 * @When /^I execute the command$/
	 */
	public function iExecuteTheNextCommand() {
		$command = array_shift($this->commands);
		if ($this->commands) {
			throw new \Exception('There are still ' . count($this->commands) . ' commands to be executed. Call them by "first", "next" or "last"');
		}
		$this->commandBus->delegate($command);
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
	 * @throws \Exception
	 */
	protected function resolveClassName($className, $prefix = '', $suffix = '') {
		return $this->classNameResolver->resolveClassName($this->package, $className, $prefix, $suffix);
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
