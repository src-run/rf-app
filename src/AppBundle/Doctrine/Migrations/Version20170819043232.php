<?php

namespace Rf\AppBundle\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170819043232 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sys_search_index_logs CHANGE object_identity object_identity VARCHAR(64) NOT NULL, CHANGE object_class object_class VARCHAR(384) NOT NULL, CHANGE object_hash object_hash VARCHAR(128) DEFAULT NULL');
        $this->addSql('ALTER TABLE sys_search_index_maps CHANGE object_identity object_identity VARCHAR(64) NOT NULL, CHANGE object_class object_class VARCHAR(384) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sys_search_index_logs CHANGE object_identity object_identity VARCHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE object_class object_class VARCHAR(256) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE object_hash object_hash VARCHAR(64) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE sys_search_index_maps CHANGE object_identity object_identity VARCHAR(36) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE object_class object_class VARCHAR(256) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
