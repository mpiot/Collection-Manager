<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180309155911 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO `user` (`id`, `email`, `password`, `roles`, `enabled`, `first_name`, `last_name`, `created`, `updated`) VALUES ("1", "admin", "$argon2i$v=19$m=1024,t=2,p=2$UWhFR2psbVM1WUZUT0ZNLg$FBKCWfk/1s3O1/Kfc14cQdSqo9FTdaRdMHbpZg+KdLE", "a:1:{i:0;s:16:\"ROLE_SUPER_ADMIN\";}", "1", "admin", "admin", "2018-03-08 17:00:00", "2018-03-08 17:00:00")');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM `user` WHERE `id`="1";');
    }
}
