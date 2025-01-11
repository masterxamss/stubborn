<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250107203944 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD delivery_address JSON NOT NULL, DROP street, DROP city, DROP zip, DROP country, DROP state');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD street VARCHAR(255) NOT NULL, ADD city VARCHAR(255) NOT NULL, ADD zip VARCHAR(255) NOT NULL, ADD country VARCHAR(255) NOT NULL, ADD state VARCHAR(255) NOT NULL, DROP delivery_address');
    }
}
