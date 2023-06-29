<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210311134036 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->connection->executeQuery('CREATE TABLE note (
            id INTEGER PRIMARY KEY NOT NULL,
            content VARCHAR(100),
            collab_id INTEGER,
            author_id INTEGER,
            published_at DATETIME,
            mindset FLOAT
        )');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->connection->executeQuery('DROP TABLE note');
    }
}
