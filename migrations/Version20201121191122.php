<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201121191122 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adresse (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, street VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, code_postal VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, first TINYINT(1) NOT NULL, INDEX IDX_C35F0816A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE adresse ADD CONSTRAINT FK_C35F0816A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE commande ADD shipping_address_id INT NOT NULL, ADD billing_address_id INT NOT NULL');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D4D4CFF2B FOREIGN KEY (shipping_address_id) REFERENCES adresse (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D79D0C0E4 FOREIGN KEY (billing_address_id) REFERENCES adresse (id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67D4D4CFF2B ON commande (shipping_address_id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67D79D0C0E4 ON commande (billing_address_id)');
        $this->addSql('ALTER TABLE user DROP adresse, DROP ville, DROP pays, DROP code_postal');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D4D4CFF2B');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D79D0C0E4');
        $this->addSql('DROP TABLE adresse');
        $this->addSql('DROP INDEX IDX_6EEAA67D4D4CFF2B ON commande');
        $this->addSql('DROP INDEX IDX_6EEAA67D79D0C0E4 ON commande');
        $this->addSql('ALTER TABLE commande DROP shipping_address_id, DROP billing_address_id');
        $this->addSql('ALTER TABLE user ADD adresse VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD ville VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD pays VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD code_postal VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
