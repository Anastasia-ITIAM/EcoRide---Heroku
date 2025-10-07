<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour créer la table trip_review (MySQL)
 */
final class Version20251007200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create trip_review table for storing trip reviews in MySQL';
    }

    public function up(Schema $schema): void
    {
        // Créer la table trip_review
        $this->addSql('CREATE TABLE trip_review (
            id INT AUTO_INCREMENT NOT NULL,
            trip_id INT NOT NULL,
            user_id INT NOT NULL,
            user_pseudo VARCHAR(255) NOT NULL,
            comment LONGTEXT NOT NULL,
            rating INT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_trip_id (trip_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // Supprimer la table trip_review
        $this->addSql('DROP TABLE trip_review');
    }
}

