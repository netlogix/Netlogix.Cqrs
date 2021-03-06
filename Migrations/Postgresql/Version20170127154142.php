<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create command log table
 */
class Version20170127154142 extends AbstractMigration
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

		$this->addSql("ALTER TABLE netlogix_cqrs_log_commandlogentry ADD COLUMN exception BYTEA DEFAULT NULL");
		$this->addSql('COMMENT ON COLUMN netlogix_cqrs_log_commandlogentry.exception IS \'(DC2Type:exceptionobject)\'');
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema)
	{
		$this->abortIf($this->connection->getDatabasePlatform()
				->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');

		$this->addSql('ALTER TABLE netlogix_cqrs_log_commandlogentry DROP COLUMN exception');
	}
}
