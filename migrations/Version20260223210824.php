<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223210824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, emoji VARCHAR(10) NOT NULL, category VARCHAR(20) NOT NULL, xp_reward INT NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE badge (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, emoji VARCHAR(10) NOT NULL, hint VARCHAR(255) DEFAULT NULL, xp_required INT DEFAULT 0 NOT NULL, activities_required INT DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_FEF0481D989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE resid_event (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, emoji VARCHAR(10) DEFAULT NULL, date DATE NOT NULL, time VARCHAR(50) DEFAULT NULL, place VARCHAR(150) DEFAULT NULL, xp_reward INT DEFAULT 0 NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) DEFAULT NULL, room VARCHAR(20) DEFAULT NULL, building VARCHAR(10) DEFAULT NULL, residence VARCHAR(150) DEFAULT NULL, residence_code VARCHAR(50) DEFAULT NULL, xp INT DEFAULT 0 NOT NULL, xp_max INT DEFAULT 500 NOT NULL, level VARCHAR(20) DEFAULT \'Bronze\' NOT NULL, badges JSON NOT NULL, color VARCHAR(7) DEFAULT \'#6c63ff\' NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_activity (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) DEFAULT \'not_started\' NOT NULL, progress INT DEFAULT 0 NOT NULL, started_at DATETIME DEFAULT NULL, completed_at DATETIME DEFAULT NULL, user_id INT NOT NULL, activity_id INT NOT NULL, INDEX IDX_4CF9ED5AA76ED395 (user_id), INDEX IDX_4CF9ED5A81C06096 (activity_id), UNIQUE INDEX user_activity_unique (user_id, activity_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user_activity ADD CONSTRAINT FK_4CF9ED5AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_activity ADD CONSTRAINT FK_4CF9ED5A81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_activity DROP FOREIGN KEY FK_4CF9ED5AA76ED395');
        $this->addSql('ALTER TABLE user_activity DROP FOREIGN KEY FK_4CF9ED5A81C06096');
        $this->addSql('DROP TABLE activity');
        $this->addSql('DROP TABLE badge');
        $this->addSql('DROP TABLE resid_event');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE user_activity');
    }
}
