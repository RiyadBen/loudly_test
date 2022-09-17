<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220917031952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invitation (id INT AUTO_INCREMENT NOT NULL, sender VARCHAR(191) DEFAULT NULL, receiver VARCHAR(191) DEFAULT NULL, message VARCHAR(255) NOT NULL, status INT NOT NULL, INDEX IDX_F11D61A25F004ACF (sender), INDEX IDX_F11D61A23DB88C96 (receiver), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (email VARCHAR(191) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(email)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A25F004ACF FOREIGN KEY (sender) REFERENCES user (email)');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A23DB88C96 FOREIGN KEY (receiver) REFERENCES user (email)');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A25F004ACF');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A23DB88C96');
        $this->addSql('DROP TABLE invitation');
        $this->addSql('DROP TABLE user');
    }
}
