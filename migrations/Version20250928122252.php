<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250928122252 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE coupon ALTER code TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE orders ALTER coupon_id TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE orders ALTER tax_number TYPE VARCHAR(30)');
        $this->addSql('ALTER TABLE orders ALTER payment_processor TYPE VARCHAR(30)');
        $this->addSql('ALTER TABLE orders ALTER payment_status TYPE VARCHAR(30)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE coupon ALTER code TYPE VARCHAR(32)');
        $this->addSql('ALTER TABLE orders ALTER coupon_id TYPE VARCHAR(32)');
        $this->addSql('ALTER TABLE orders ALTER tax_number TYPE VARCHAR(64)');
        $this->addSql('ALTER TABLE orders ALTER payment_processor TYPE VARCHAR(32)');
        $this->addSql('ALTER TABLE orders ALTER payment_status TYPE VARCHAR(32)');
    }
}
