<?php declare(strict_types=1);

namespace App\Bookings\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260729120100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create bookings table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE bookings (
                id CHAR(26) NOT NULL,
                session_id CHAR(26) NOT NULL,
                user_id CHAR(26) NOT NULL,
                seats INT NOT NULL,
                status VARCHAR(20) NOT NULL,
                total_price_amount INT NOT NULL,
                total_price_currency VARCHAR(3) NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME DEFAULT NULL,
                INDEX idx_bookings_session (session_id),
                INDEX idx_bookings_user (user_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE bookings');
    }
}
