<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200322031106 extends AbstractMigration {
    public function getDescription(): string {
        return 'Data dump';
    }

    public function up(Schema $schema): void {
        $this->addSql('INSERT INTO user (name, username,email,password,default_currency) '
            . "VALUES ('Anderson', 'andsalves', 'ands.alves.nunes@gmail.com', 'AkCgH10PXOVvTDLALBkqe1kAAvrvrt3AGYGfvrfHfIJYNZNdP26CINp5TXwEQOdV0tV7yW5hsBMOEZRd/mIJ0w==', 'USD')");
    }

    public function down(Schema $schema): void {
        $this->addSql("DELETE FROM user WHERE username='andsalves'");
    }
}
