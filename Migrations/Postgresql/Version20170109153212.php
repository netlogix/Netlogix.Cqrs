<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create command log table
 */
class Version20170109153212 extends AbstractMigration
{

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return '';
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema)
	{
		$this->abortIf($this->connection->getDatabasePlatform()
				->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');

		$this->addSql('CREATE TABLE netlogix_cqrs_log_commandlogentry (commandid UUID NOT NULL, commandtype VARCHAR(255) NOT NULL, executiondateandtime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, command TEXT NOT NULL, PRIMARY KEY(commandid))');
		$this->addSql('COMMENT ON COLUMN netlogix_cqrs_log_commandlogentry.command IS \'(DC2Type:object)\'');
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema)
	{
		$this->abortIf($this->connection->getDatabasePlatform()
				->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');

		$this->addSql('DROP TABLE netlogix_cqrs_log_commandlogentry');
	}
}
