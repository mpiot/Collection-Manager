<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180302133139 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE plasmid (id INT AUTO_INCREMENT NOT NULL, group_id INT DEFAULT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, slug VARCHAR(128) NOT NULL, autoName VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, gen_bank_name VARCHAR(255) DEFAULT NULL, gen_bank_size INT DEFAULT NULL, gen_bank_updated_at DATETIME DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_6D05BC27FE54D947 (group_id), INDEX IDX_6D05BC27DE12AB56 (created_by), INDEX IDX_6D05BC2716FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plasmid_primer (plasmid_id INT NOT NULL, primer_id INT NOT NULL, INDEX IDX_6DFE3F463598003 (plasmid_id), INDEX IDX_6DFE3F45B7FD82C (primer_id), PRIMARY KEY(plasmid_id, primer_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipment (id INT AUTO_INCREMENT NOT NULL, brand_id INT DEFAULT NULL, seller_id INT DEFAULT NULL, location_id INT DEFAULT NULL, group_id INT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(128) NOT NULL, description LONGTEXT DEFAULT NULL, serialNumber VARCHAR(255) DEFAULT NULL, purchaseDate DATETIME DEFAULT NULL, model VARCHAR(255) NOT NULL, inventoryNumber VARCHAR(255) NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_D338D58344F5D008 (brand_id), INDEX IDX_D338D5838DE820D9 (seller_id), INDEX IDX_D338D58364D218E (location_id), INDEX IDX_D338D583FE54D947 (group_id), INDEX IDX_D338D583DE12AB56 (created_by), INDEX IDX_D338D58316FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE species (id INT AUTO_INCREMENT NOT NULL, genus_id INT DEFAULT NULL, main_species_id INT DEFAULT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, slug VARCHAR(128) NOT NULL, name VARCHAR(255) NOT NULL, taxId INT DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, UNIQUE INDEX UNIQ_A50FF712989D9B62 (slug), INDEX IDX_A50FF71285C4074C (genus_id), INDEX IDX_A50FF7128A59AC03 (main_species_id), INDEX IDX_A50FF712DE12AB56 (created_by), INDEX IDX_A50FF71216FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE genus (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, UNIQUE INDEX UNIQ_38C5106E5E237E06 (name), INDEX IDX_38C5106EDE12AB56 (created_by), INDEX IDX_38C5106E16FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, brand_id INT NOT NULL, seller_id INT NOT NULL, group_id INT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(128) NOT NULL, brandReference VARCHAR(255) NOT NULL, sellerReference VARCHAR(255) NOT NULL, catalogPrice INT NOT NULL, negotiatedPrice INT NOT NULL, quote_name VARCHAR(255) DEFAULT NULL, quote_size INT DEFAULT NULL, quote_updated_at DATETIME DEFAULT NULL, manual_name VARCHAR(255) DEFAULT NULL, manual_size INT DEFAULT NULL, manual_updated_at DATETIME DEFAULT NULL, packedBy INT NOT NULL, packagingUnit VARCHAR(255) NOT NULL, storageUnit VARCHAR(255) NOT NULL, stock INT NOT NULL, stockWarningAlert INT NOT NULL, stockDangerAlert INT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_D34A04AD64D218E (location_id), INDEX IDX_D34A04AD44F5D008 (brand_id), INDEX IDX_D34A04AD8DE820D9 (seller_id), INDEX IDX_D34A04ADFE54D947 (group_id), INDEX IDX_D34A04ADDE12AB56 (created_by), INDEX IDX_D34A04AD16FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(128) NOT NULL, last_strain_number INT NOT NULL, last_plasmid_number INT NOT NULL, last_primer_number INT NOT NULL, UNIQUE INDEX UNIQ_6DC044C55E237E06 (name), UNIQUE INDEX UNIQ_6DC044C5989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE group_administrators (group_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_9FB7B81DFE54D947 (group_id), INDEX IDX_9FB7B81DA76ED395 (user_id), PRIMARY KEY(group_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE group_members (group_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_C3A086F3FE54D947 (group_id), INDEX IDX_C3A086F3A76ED395 (user_id), PRIMARY KEY(group_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, tree_root INT DEFAULT NULL, parent_id INT DEFAULT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, lft INT NOT NULL, lvl INT NOT NULL, rgt INT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_5E9E89CBA977936C (tree_root), INDEX IDX_5E9E89CB727ACA70 (parent_id), INDEX IDX_5E9E89CBDE12AB56 (created_by), INDEX IDX_5E9E89CB16FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tube (id INT AUTO_INCREMENT NOT NULL, box_id INT DEFAULT NULL, strain_id INT DEFAULT NULL, plasmid_id INT DEFAULT NULL, primer_id INT DEFAULT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, cell INT NOT NULL, cellName VARCHAR(255) NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_FD30DE9ED8177B3F (box_id), INDEX IDX_FD30DE9E69B9E007 (strain_id), INDEX IDX_FD30DE9E63598003 (plasmid_id), INDEX IDX_FD30DE9E5B7FD82C (primer_id), INDEX IDX_FD30DE9EDE12AB56 (created_by), INDEX IDX_FD30DE9E16FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE strain (id INT AUTO_INCREMENT NOT NULL, species INT DEFAULT NULL, group_id INT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, slug VARCHAR(128) NOT NULL, discriminator VARCHAR(255) NOT NULL, auto_name VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, unique_code VARCHAR(255) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, sequenced TINYINT(1) NOT NULL, genotype LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, biologicalOrigin VARCHAR(255) DEFAULT NULL, source VARCHAR(255) DEFAULT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, UNIQUE INDEX UNIQ_A630CD72989D9B62 (slug), INDEX IDX_A630CD72A50FF712 (species), INDEX IDX_A630CD72FE54D947 (group_id), INDEX IDX_A630CD72DE12AB56 (created_by), INDEX IDX_A630CD7216FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE strains_parents (strain_source INT NOT NULL, strain_target INT NOT NULL, INDEX IDX_9BCD26D4B919AAC7 (strain_source), INDEX IDX_9BCD26D4A0FCFA48 (strain_target), PRIMARY KEY(strain_source, strain_target)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE brand (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(128) NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, UNIQUE INDEX UNIQ_1C52F9585E237E06 (name), UNIQUE INDEX UNIQ_1C52F958989D9B62 (slug), INDEX IDX_1C52F958DE12AB56 (created_by), INDEX IDX_1C52F95816FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, favorite_group_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', enabled TINYINT(1) NOT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D6495403C1B6 (favorite_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE strain_plasmid (id INT AUTO_INCREMENT NOT NULL, strain_id INT NOT NULL, plasmid_id INT NOT NULL, state VARCHAR(255) NOT NULL, INDEX IDX_A1EF586769B9E007 (strain_id), INDEX IDX_A1EF586763598003 (plasmid_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE primer (id INT AUTO_INCREMENT NOT NULL, group_id INT DEFAULT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, slug VARCHAR(128) NOT NULL, autoName VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, orientation VARCHAR(255) DEFAULT NULL, sequence VARCHAR(255) NOT NULL, fivePrimeExtension VARCHAR(255) DEFAULT NULL, labelMarker VARCHAR(255) DEFAULT NULL, hybridationTemp DOUBLE PRECISION DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_995270C5FE54D947 (group_id), INDEX IDX_995270C5DE12AB56 (created_by), INDEX IDX_995270C516FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_movement (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, created_by_id INT NOT NULL, updated_by_id INT NOT NULL, movement INT NOT NULL, comment LONGTEXT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_3F6DFF604584665A (product_id), INDEX IDX_3F6DFF60B03A8386 (created_by_id), INDEX IDX_3F6DFF60896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE seller (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(128) NOT NULL, offerReference VARCHAR(255) DEFAULT NULL, offer_name VARCHAR(255) DEFAULT NULL, offer_size INT DEFAULT NULL, offer_updated_at DATETIME DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, UNIQUE INDEX UNIQ_FB1AD3FC5E237E06 (name), UNIQUE INDEX UNIQ_FB1AD3FC989D9B62 (slug), INDEX IDX_FB1AD3FCDE12AB56 (created_by), INDEX IDX_FB1AD3FC16FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE box (id INT AUTO_INCREMENT NOT NULL, group_id INT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, slug VARCHAR(128) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, freezer VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, colNumber INT NOT NULL, rowNumber INT NOT NULL, freeSpace INT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_8A9483AFE54D947 (group_id), INDEX IDX_8A9483ADE12AB56 (created_by), INDEX IDX_8A9483A16FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE plasmid ADD CONSTRAINT FK_6D05BC27FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE plasmid ADD CONSTRAINT FK_6D05BC27DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE plasmid ADD CONSTRAINT FK_6D05BC2716FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE plasmid_primer ADD CONSTRAINT FK_6DFE3F463598003 FOREIGN KEY (plasmid_id) REFERENCES plasmid (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE plasmid_primer ADD CONSTRAINT FK_6DFE3F45B7FD82C FOREIGN KEY (primer_id) REFERENCES primer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_D338D58344F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_D338D5838DE820D9 FOREIGN KEY (seller_id) REFERENCES seller (id)');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_D338D58364D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_D338D583FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_D338D583DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_D338D58316FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE species ADD CONSTRAINT FK_A50FF71285C4074C FOREIGN KEY (genus_id) REFERENCES genus (id)');
        $this->addSql('ALTER TABLE species ADD CONSTRAINT FK_A50FF7128A59AC03 FOREIGN KEY (main_species_id) REFERENCES species (id)');
        $this->addSql('ALTER TABLE species ADD CONSTRAINT FK_A50FF712DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE species ADD CONSTRAINT FK_A50FF71216FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE genus ADD CONSTRAINT FK_38C5106EDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE genus ADD CONSTRAINT FK_38C5106E16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD64D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD44F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD8DE820D9 FOREIGN KEY (seller_id) REFERENCES seller (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE group_administrators ADD CONSTRAINT FK_9FB7B81DFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE group_administrators ADD CONSTRAINT FK_9FB7B81DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE group_members ADD CONSTRAINT FK_C3A086F3FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE group_members ADD CONSTRAINT FK_C3A086F3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBA977936C FOREIGN KEY (tree_root) REFERENCES location (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB727ACA70 FOREIGN KEY (parent_id) REFERENCES location (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tube ADD CONSTRAINT FK_FD30DE9ED8177B3F FOREIGN KEY (box_id) REFERENCES box (id)');
        $this->addSql('ALTER TABLE tube ADD CONSTRAINT FK_FD30DE9E69B9E007 FOREIGN KEY (strain_id) REFERENCES strain (id)');
        $this->addSql('ALTER TABLE tube ADD CONSTRAINT FK_FD30DE9E63598003 FOREIGN KEY (plasmid_id) REFERENCES plasmid (id)');
        $this->addSql('ALTER TABLE tube ADD CONSTRAINT FK_FD30DE9E5B7FD82C FOREIGN KEY (primer_id) REFERENCES primer (id)');
        $this->addSql('ALTER TABLE tube ADD CONSTRAINT FK_FD30DE9EDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tube ADD CONSTRAINT FK_FD30DE9E16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE strain ADD CONSTRAINT FK_A630CD72A50FF712 FOREIGN KEY (species) REFERENCES species (id)');
        $this->addSql('ALTER TABLE strain ADD CONSTRAINT FK_A630CD72FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE strain ADD CONSTRAINT FK_A630CD72DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE strain ADD CONSTRAINT FK_A630CD7216FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE strains_parents ADD CONSTRAINT FK_9BCD26D4B919AAC7 FOREIGN KEY (strain_source) REFERENCES strain (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE strains_parents ADD CONSTRAINT FK_9BCD26D4A0FCFA48 FOREIGN KEY (strain_target) REFERENCES strain (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F958DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F95816FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6495403C1B6 FOREIGN KEY (favorite_group_id) REFERENCES `group` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE strain_plasmid ADD CONSTRAINT FK_A1EF586769B9E007 FOREIGN KEY (strain_id) REFERENCES strain (id)');
        $this->addSql('ALTER TABLE strain_plasmid ADD CONSTRAINT FK_A1EF586763598003 FOREIGN KEY (plasmid_id) REFERENCES plasmid (id)');
        $this->addSql('ALTER TABLE primer ADD CONSTRAINT FK_995270C5FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE primer ADD CONSTRAINT FK_995270C5DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE primer ADD CONSTRAINT FK_995270C516FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product_movement ADD CONSTRAINT FK_3F6DFF604584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_movement ADD CONSTRAINT FK_3F6DFF60B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product_movement ADD CONSTRAINT FK_3F6DFF60896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE seller ADD CONSTRAINT FK_FB1AD3FCDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE seller ADD CONSTRAINT FK_FB1AD3FC16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE box ADD CONSTRAINT FK_8A9483AFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE box ADD CONSTRAINT FK_8A9483ADE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE box ADD CONSTRAINT FK_8A9483A16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE plasmid_primer DROP FOREIGN KEY FK_6DFE3F463598003');
        $this->addSql('ALTER TABLE tube DROP FOREIGN KEY FK_FD30DE9E63598003');
        $this->addSql('ALTER TABLE strain_plasmid DROP FOREIGN KEY FK_A1EF586763598003');
        $this->addSql('ALTER TABLE species DROP FOREIGN KEY FK_A50FF7128A59AC03');
        $this->addSql('ALTER TABLE strain DROP FOREIGN KEY FK_A630CD72A50FF712');
        $this->addSql('ALTER TABLE species DROP FOREIGN KEY FK_A50FF71285C4074C');
        $this->addSql('ALTER TABLE product_movement DROP FOREIGN KEY FK_3F6DFF604584665A');
        $this->addSql('ALTER TABLE plasmid DROP FOREIGN KEY FK_6D05BC27FE54D947');
        $this->addSql('ALTER TABLE equipment DROP FOREIGN KEY FK_D338D583FE54D947');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADFE54D947');
        $this->addSql('ALTER TABLE group_administrators DROP FOREIGN KEY FK_9FB7B81DFE54D947');
        $this->addSql('ALTER TABLE group_members DROP FOREIGN KEY FK_C3A086F3FE54D947');
        $this->addSql('ALTER TABLE strain DROP FOREIGN KEY FK_A630CD72FE54D947');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6495403C1B6');
        $this->addSql('ALTER TABLE primer DROP FOREIGN KEY FK_995270C5FE54D947');
        $this->addSql('ALTER TABLE box DROP FOREIGN KEY FK_8A9483AFE54D947');
        $this->addSql('ALTER TABLE equipment DROP FOREIGN KEY FK_D338D58364D218E');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD64D218E');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBA977936C');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CB727ACA70');
        $this->addSql('ALTER TABLE tube DROP FOREIGN KEY FK_FD30DE9E69B9E007');
        $this->addSql('ALTER TABLE strains_parents DROP FOREIGN KEY FK_9BCD26D4B919AAC7');
        $this->addSql('ALTER TABLE strains_parents DROP FOREIGN KEY FK_9BCD26D4A0FCFA48');
        $this->addSql('ALTER TABLE strain_plasmid DROP FOREIGN KEY FK_A1EF586769B9E007');
        $this->addSql('ALTER TABLE equipment DROP FOREIGN KEY FK_D338D58344F5D008');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD44F5D008');
        $this->addSql('ALTER TABLE plasmid DROP FOREIGN KEY FK_6D05BC27DE12AB56');
        $this->addSql('ALTER TABLE plasmid DROP FOREIGN KEY FK_6D05BC2716FE72E1');
        $this->addSql('ALTER TABLE equipment DROP FOREIGN KEY FK_D338D583DE12AB56');
        $this->addSql('ALTER TABLE equipment DROP FOREIGN KEY FK_D338D58316FE72E1');
        $this->addSql('ALTER TABLE species DROP FOREIGN KEY FK_A50FF712DE12AB56');
        $this->addSql('ALTER TABLE species DROP FOREIGN KEY FK_A50FF71216FE72E1');
        $this->addSql('ALTER TABLE genus DROP FOREIGN KEY FK_38C5106EDE12AB56');
        $this->addSql('ALTER TABLE genus DROP FOREIGN KEY FK_38C5106E16FE72E1');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADDE12AB56');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD16FE72E1');
        $this->addSql('ALTER TABLE group_administrators DROP FOREIGN KEY FK_9FB7B81DA76ED395');
        $this->addSql('ALTER TABLE group_members DROP FOREIGN KEY FK_C3A086F3A76ED395');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBDE12AB56');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CB16FE72E1');
        $this->addSql('ALTER TABLE tube DROP FOREIGN KEY FK_FD30DE9EDE12AB56');
        $this->addSql('ALTER TABLE tube DROP FOREIGN KEY FK_FD30DE9E16FE72E1');
        $this->addSql('ALTER TABLE strain DROP FOREIGN KEY FK_A630CD72DE12AB56');
        $this->addSql('ALTER TABLE strain DROP FOREIGN KEY FK_A630CD7216FE72E1');
        $this->addSql('ALTER TABLE brand DROP FOREIGN KEY FK_1C52F958DE12AB56');
        $this->addSql('ALTER TABLE brand DROP FOREIGN KEY FK_1C52F95816FE72E1');
        $this->addSql('ALTER TABLE primer DROP FOREIGN KEY FK_995270C5DE12AB56');
        $this->addSql('ALTER TABLE primer DROP FOREIGN KEY FK_995270C516FE72E1');
        $this->addSql('ALTER TABLE product_movement DROP FOREIGN KEY FK_3F6DFF60B03A8386');
        $this->addSql('ALTER TABLE product_movement DROP FOREIGN KEY FK_3F6DFF60896DBBDE');
        $this->addSql('ALTER TABLE seller DROP FOREIGN KEY FK_FB1AD3FCDE12AB56');
        $this->addSql('ALTER TABLE seller DROP FOREIGN KEY FK_FB1AD3FC16FE72E1');
        $this->addSql('ALTER TABLE box DROP FOREIGN KEY FK_8A9483ADE12AB56');
        $this->addSql('ALTER TABLE box DROP FOREIGN KEY FK_8A9483A16FE72E1');
        $this->addSql('ALTER TABLE plasmid_primer DROP FOREIGN KEY FK_6DFE3F45B7FD82C');
        $this->addSql('ALTER TABLE tube DROP FOREIGN KEY FK_FD30DE9E5B7FD82C');
        $this->addSql('ALTER TABLE equipment DROP FOREIGN KEY FK_D338D5838DE820D9');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD8DE820D9');
        $this->addSql('ALTER TABLE tube DROP FOREIGN KEY FK_FD30DE9ED8177B3F');
        $this->addSql('DROP TABLE plasmid');
        $this->addSql('DROP TABLE plasmid_primer');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE species');
        $this->addSql('DROP TABLE genus');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE group_administrators');
        $this->addSql('DROP TABLE group_members');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE tube');
        $this->addSql('DROP TABLE strain');
        $this->addSql('DROP TABLE strains_parents');
        $this->addSql('DROP TABLE brand');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE strain_plasmid');
        $this->addSql('DROP TABLE primer');
        $this->addSql('DROP TABLE product_movement');
        $this->addSql('DROP TABLE seller');
        $this->addSql('DROP TABLE box');
    }
}
