<?php declare(strict_types=1);

namespace App\Experiences\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260729120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create experiences and sessions tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE experiences (
                id CHAR(26) NOT NULL,
                provider_id CHAR(26) NOT NULL,
                title VARCHAR(255) NOT NULL,
                description LONGTEXT NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME DEFAULT NULL,
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        ');

        $this->addSql('
            CREATE TABLE sessions (
                id CHAR(26) NOT NULL,
                experience_id CHAR(26) NOT NULL,
                starts_at DATETIME NOT NULL,
                max_capacity INT NOT NULL,
                available_spots INT NOT NULL,
                price_amount INT NOT NULL,
                price_currency VARCHAR(3) NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME DEFAULT NULL,
                INDEX idx_sessions_experience_starts_at (experience_id, starts_at),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE sessions');
        $this->addSql('DROP TABLE experiences');
    }
}
