<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add command status to command log entry
 */
class Version20170217150934 extends AbstractMigration
{

	/**
	 * @return string
	 */
	public function getDescription(): string
	{
		return '';
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');

		$this->addSql('ALTER TABLE netlogix_cqrs_log_commandlogentry ADD status INT');
		// commands until now have only been logged when successful
		$this->addSql("UPDATE netlogix_cqrs_log_commandlogentry SET status = 3");
		$this->addSql('ALTER TABLE netlogix_cqrs_log_commandlogentry ALTER status SET NOT NULL');
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema): void
	{
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');

		$this->addSql('ALTER TABLE netlogix_cqrs_log_commandlogentry DROP status');
	}
}
