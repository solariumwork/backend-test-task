<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250924222649 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE orders_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE orders ALTER id TYPE INT USING id::integer');
        $this->addSql('ALTER TABLE orders ALTER id TYPE INT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE orders_id_seq CASCADE');
        $this->addSql('ALTER TABLE orders ALTER id TYPE VARCHAR(64)');
    }
}
