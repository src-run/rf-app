<?php

namespace Rf\AppBundle\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170817054551 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sys_revision_logs (
          uuid VARCHAR(36) NOT NULL, 
          action VARCHAR(8) NOT NULL, 
          created DATETIME NOT NULL, 
          object_id VARCHAR(64) DEFAULT NULL, 
          object_class VARCHAR(255) NOT NULL, 
          version INT UNSIGNED NOT NULL, 
          data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', 
          username VARCHAR(255) DEFAULT NULL, 
          INDEX revision_class_idx (object_class), 
          INDEX revision_date_idx (created), 
          INDEX revision_user_idx (username), 
          INDEX revision_version_idx (
            object_id, object_class, username
          ), 
          PRIMARY KEY(uuid)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE sys_search_word_stems (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          stem VARCHAR(128) NOT NULL, 
          UNIQUE INDEX UNIQ_98C4883120BF92E5 (stem), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE sys_email_spool (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          created DATETIME NOT NULL, 
          updated DATETIME NOT NULL, 
          status VARCHAR(2) DEFAULT NULL, 
          environment VARCHAR(8) DEFAULT NULL, 
          message LONGTEXT NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE content_articles (
          uuid VARCHAR(36) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          created DATETIME NOT NULL, 
          updated DATETIME NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          content LONGTEXT NOT NULL, 
          PRIMARY KEY(uuid)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE sys_search_rev_index (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          stem_id INT UNSIGNED DEFAULT NULL, 
          article_uuid VARCHAR(36) DEFAULT NULL, 
          position BIGINT UNSIGNED NOT NULL, 
          INDEX IDX_A58EF212D1C191DE (stem_id), 
          INDEX IDX_A58EF212613DD7A7 (article_uuid), 
          INDEX id_stem_article_idx (id, stem_id, article_uuid), 
          INDEX id_article_stem_idx (id, article_uuid, stem_id), 
          UNIQUE INDEX stem_article_position_uniq (stem_id, article_uuid, position), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('ALTER TABLE 
          sys_search_rev_index 
        ADD 
          CONSTRAINT FK_A58EF212D1C191DE FOREIGN KEY (stem_id) REFERENCES sys_search_word_stems (id)');
        $this->addSql('ALTER TABLE 
          sys_search_rev_index 
        ADD 
          CONSTRAINT FK_A58EF212613DD7A7 FOREIGN KEY (article_uuid) REFERENCES content_articles (uuid)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sys_search_rev_index DROP FOREIGN KEY FK_A58EF212D1C191DE');
        $this->addSql('ALTER TABLE sys_search_rev_index DROP FOREIGN KEY FK_A58EF212613DD7A7');
        $this->addSql('DROP TABLE sys_revision_logs');
        $this->addSql('DROP TABLE sys_search_word_stems');
        $this->addSql('DROP TABLE sys_email_spool');
        $this->addSql('DROP TABLE content_articles');
        $this->addSql('DROP TABLE sys_search_rev_index');
    }
}
