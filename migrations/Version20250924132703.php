<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250924132703 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE product_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE coupon (code VARCHAR(32) NOT NULL, type VARCHAR(10) NOT NULL, value INT NOT NULL, active BOOLEAN NOT NULL, PRIMARY KEY(code))');
        $this->addSql('CREATE TABLE orders (id VARCHAR(64) NOT NULL, coupon_id VARCHAR(32) DEFAULT NULL, product_id INT NOT NULL, tax_number VARCHAR(64) NOT NULL, payment_processor VARCHAR(32) NOT NULL, price_price_cents INT NOT NULL, price_currency VARCHAR(3) NOT NULL, total_price_cents INT NOT NULL, total_currency VARCHAR(3) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E52FFDEE66C5951B ON orders (coupon_id)');
        $this->addSql('CREATE TABLE product (id INT NOT NULL, name VARCHAR(255) NOT NULL, price_price_cents INT NOT NULL, price_currency VARCHAR(3) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE66C5951B FOREIGN KEY (coupon_id) REFERENCES coupon (code) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE product_id_seq CASCADE');
        $this->addSql('ALTER TABLE orders DROP CONSTRAINT FK_E52FFDEE66C5951B');
        $this->addSql('DROP TABLE coupon');
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE product');
    }
}
