<?php

namespace Rf\AppBundle\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170819043049 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sys_search_index_maps (id INT UNSIGNED AUTO_INCREMENT NOT NULL, stem_id INT UNSIGNED DEFAULT NULL, position BIGINT UNSIGNED NOT NULL, object_identity VARCHAR(36) NOT NULL, object_class VARCHAR(256) NOT NULL, INDEX IDX_6197356FD1C191DE (stem_id), UNIQUE INDEX stem_position_object_uniq (stem_id, position, object_identity, object_class), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = COMPRESSED');
        $this->addSql('CREATE TABLE sys_search_word_stems (id INT UNSIGNED AUTO_INCREMENT NOT NULL, stem VARCHAR(128) NOT NULL, UNIQUE INDEX UNIQ_98C4883120BF92E5 (stem), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = COMPRESSED');
        $this->addSql('CREATE TABLE sys_search_index_logs (id INT UNSIGNED AUTO_INCREMENT NOT NULL, updated DATETIME NOT NULL, success TINYINT(1) NOT NULL, object_identity VARCHAR(36) NOT NULL, object_class VARCHAR(256) NOT NULL, object_hash VARCHAR(64) DEFAULT NULL, INDEX identity_class_hash_idx (object_identity, object_class, object_hash), UNIQUE INDEX stem_position_object_uniq (object_identity, object_class), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = COMPRESSED');
        $this->addSql('CREATE TABLE sys_revision_logs (uuid VARCHAR(36) NOT NULL, action VARCHAR(8) NOT NULL, created DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(255) NOT NULL, version INT UNSIGNED NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', username VARCHAR(255) DEFAULT NULL, INDEX revision_class_idx (object_class), INDEX revision_date_idx (created), INDEX revision_user_idx (username), INDEX revision_version_idx (object_id, object_class, username), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = COMPRESSED');
        $this->addSql('CREATE TABLE sys_email_spool (id INT UNSIGNED AUTO_INCREMENT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, status VARCHAR(2) DEFAULT NULL, environment VARCHAR(8) DEFAULT NULL, message LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = COMPRESSED');
        $this->addSql('CREATE TABLE content_articles (uuid VARCHAR(36) NOT NULL, slug VARCHAR(255) NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = COMPRESSED');
        $this->addSql('ALTER TABLE sys_search_index_maps ADD CONSTRAINT FK_6197356FD1C191DE FOREIGN KEY (stem_id) REFERENCES sys_search_word_stems (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sys_search_index_maps DROP FOREIGN KEY FK_6197356FD1C191DE');
        $this->addSql('DROP TABLE sys_search_index_maps');
        $this->addSql('DROP TABLE sys_search_word_stems');
        $this->addSql('DROP TABLE sys_search_index_logs');
        $this->addSql('DROP TABLE sys_revision_logs');
        $this->addSql('DROP TABLE sys_email_spool');
        $this->addSql('DROP TABLE content_articles');
    }
}
