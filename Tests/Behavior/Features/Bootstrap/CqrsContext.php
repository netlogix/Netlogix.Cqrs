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
	 */
	public function iHaveACommandWithParameters($commandName, TableNode $parameters) {
		foreach ([$commandName, str_replace('.', '\\', $this->package) . '\\Domain\\Command\\' . $commandName . 'Command'] as $possibleCommandClass) {
			if (class_exists($possibleCommandClass)) {
				$reflectedCommand = new \ReflectionClass($possibleCommandClass);
				$this->command = $reflectedCommand->newInstanceArgs($parameters->getRowsHash());
				return;
			}
		}
		throw new \Exception('Could not find command "' . $commandName . '"');
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
	 */
	public function theDatabaseContainsAWithValues($modelName, TableNode $values) {
		$modelClass = NULL;
		foreach ([$modelName, str_replace('.', '\\', $this->package) . '\\Domain\\Model\\' . $modelName] as $possibleModelClass) {
			if (class_exists($possibleModelClass)) {
				$modelClass = $possibleModelClass;
				break;
			}
		}
		if ($modelClass === NULL) {
			throw new \Exception('Could not find model "' . $modelName . '"');
		}

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
}
