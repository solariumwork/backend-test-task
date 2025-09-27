<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250927121051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE orders ADD price_cents INT NOT NULL');
        $this->addSql('ALTER TABLE orders ADD total_cents INT NOT NULL');
        $this->addSql('ALTER TABLE orders DROP price_price_cents');
        $this->addSql('ALTER TABLE orders DROP total_price_cents');
        $this->addSql('ALTER TABLE product RENAME COLUMN price_price_cents TO price_cents');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product RENAME COLUMN price_cents TO price_price_cents');
        $this->addSql('ALTER TABLE orders ADD price_price_cents INT NOT NULL');
        $this->addSql('ALTER TABLE orders ADD total_price_cents INT NOT NULL');
        $this->addSql('ALTER TABLE orders DROP price_cents');
        $this->addSql('ALTER TABLE orders DROP total_cents');
    }
}
