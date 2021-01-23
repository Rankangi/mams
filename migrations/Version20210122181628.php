<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210122181628 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE prepare_commande (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, article_id INT NOT NULL, shipping_address_id INT DEFAULT NULL, billing_address_id INT DEFAULT NULL, amount INT NOT NULL, statut VARCHAR(255) NOT NULL, session_id VARCHAR(64) NOT NULL, INDEX IDX_ABD9FD63A76ED395 (user_id), INDEX IDX_ABD9FD637294869C (article_id), INDEX IDX_ABD9FD634D4CFF2B (shipping_address_id), INDEX IDX_ABD9FD6379D0C0E4 (billing_address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE prepare_commande ADD CONSTRAINT FK_ABD9FD63A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE prepare_commande ADD CONSTRAINT FK_ABD9FD637294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE prepare_commande ADD CONSTRAINT FK_ABD9FD634D4CFF2B FOREIGN KEY (shipping_address_id) REFERENCES adresse (id)');
        $this->addSql('ALTER TABLE prepare_commande ADD CONSTRAINT FK_ABD9FD6379D0C0E4 FOREIGN KEY (billing_address_id) REFERENCES adresse (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE prepare_commande');
    }
}
