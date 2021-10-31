<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20210418162247 extends AbstractMigration
{
    private const TABLE_NAME = 'payment_domain_events';

    public function getDescription(): string
    {
        return 'Create table for domain events of payment package';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable(self::TABLE_NAME);
        $table->addColumn('id', Types::STRING)->setLength(36);
        $table->addColumn('aggregateId', Types::STRING)->setLength(36);
        $table->addColumn('type', Types::STRING)->setLength(255);
        $table->addColumn('data', Types::JSON);
        $table->addColumn('createdAt', Types::DATETIME_IMMUTABLE);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['aggregateId']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable(self::TABLE_NAME);
    }
}
