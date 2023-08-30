<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionInit extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create all tables and init first data';
    }

    // for each file of folder _install/sql i want read the file and execute the query
    // $test = file_get_contents('./_install/*.sql');
    // $this->connection->getNativeConnection()->exec($test);
    public function up(Schema $schema) : void
    {
        $files = glob('./_install/sql/*.sql');
        foreach($files as $file) {
            $tempFileContent = file_get_contents($file);
            $this->connection->getNativeConnection()->exec($tempFileContent);
        }
    }

    public function down(Schema $schema) : void
    {
        $this->connection->executeQuery('DROP TABLE category');
        $this->connection->executeQuery('DROP TABLE configuration');
        $this->connection->executeQuery('DROP TABLE custom_color');
        $this->connection->executeQuery('DROP TABLE custom_date');
        $this->connection->executeQuery('DROP TABLE desk');
        $this->connection->executeQuery('DROP TABLE desk_date');
        $this->connection->executeQuery('DROP TABLE gantt_link');
        $this->connection->executeQuery('DROP TABLE gantt_task');
        $this->connection->executeQuery('DROP TABLE mep');
        $this->connection->executeQuery('DROP TABLE mindset');
        $this->connection->executeQuery('DROP TABLE mstoken');
        $this->connection->executeQuery('DROP TABLE o3');
        $this->connection->executeQuery('DROP TABLE office');
        $this->connection->executeQuery('DROP TABLE product');
        $this->connection->executeQuery('DROP TABLE project');
        $this->connection->executeQuery('DROP TABLE projects_products');
        $this->connection->executeQuery('DROP TABLE squad');
        $this->connection->executeQuery('DROP TABLE user_date');
        $this->connection->executeQuery('DROP TABLE users_objectivethemes');
        $this->connection->executeQuery('DROP TABLE objective_theme');
        $this->connection->executeQuery('DROP TABLE user');
        $this->connection->executeQuery('DROP TABLE users_projects');
        $this->connection->executeQuery('DROP TABLE vacation');
    }
}
