<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230604214617 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}
	
	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql(
			'CREATE TABLE permissao (id INT AUTO_INCREMENT NOT NULL, nome VARCHAR(127) NOT NULL, descricao VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
		);
		$this->addSql(
			'CREATE TABLE permissao_permissao (permissao_source INT NOT NULL, permissao_target INT NOT NULL, INDEX IDX_10D4164B6F9F262 (permissao_source), INDEX IDX_10D4164AF1CA2ED (permissao_target), PRIMARY KEY(permissao_source, permissao_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
		);
		$this->addSql(
			'CREATE TABLE usuario_permissoes (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, UNIQUE INDEX UNIQ_8E1D20E9DB38439E (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
		);
		$this->addSql(
			'CREATE TABLE usuario_permissoes_permissao (usuario_permissoes_id INT NOT NULL, permissao_id INT NOT NULL, INDEX IDX_60C5FE1A36F066EA (usuario_permissoes_id), INDEX IDX_60C5FE1AE009E574 (permissao_id), PRIMARY KEY(usuario_permissoes_id, permissao_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
		);
		$this->addSql(
			'ALTER TABLE permissao_permissao ADD CONSTRAINT FK_10D4164B6F9F262 FOREIGN KEY (permissao_source) REFERENCES permissao (id) ON DELETE CASCADE'
		);
		$this->addSql(
			'ALTER TABLE permissao_permissao ADD CONSTRAINT FK_10D4164AF1CA2ED FOREIGN KEY (permissao_target) REFERENCES permissao (id) ON DELETE CASCADE'
		);
		$this->addSql(
			'ALTER TABLE usuario_permissoes ADD CONSTRAINT FK_8E1D20E9DB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)'
		);
		$this->addSql(
			'ALTER TABLE usuario_permissoes_permissao ADD CONSTRAINT FK_60C5FE1A36F066EA FOREIGN KEY (usuario_permissoes_id) REFERENCES usuario_permissoes (id) ON DELETE CASCADE'
		);
		$this->addSql(
			'ALTER TABLE usuario_permissoes_permissao ADD CONSTRAINT FK_60C5FE1AE009E574 FOREIGN KEY (permissao_id) REFERENCES permissao (id) ON DELETE CASCADE'
		);
		
		//Adicionando permissão base de administrador
		$this->addSql("INSERT INTO permissao (nome, descricao) VALUES ('admin', 'Permissão base de Administrador')");
	}
	
	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE permissao_permissao DROP FOREIGN KEY FK_10D4164B6F9F262');
		$this->addSql('ALTER TABLE permissao_permissao DROP FOREIGN KEY FK_10D4164AF1CA2ED');
		$this->addSql('ALTER TABLE usuario_permissoes DROP FOREIGN KEY FK_8E1D20E9DB38439E');
		$this->addSql('ALTER TABLE usuario_permissoes_permissao DROP FOREIGN KEY FK_60C5FE1A36F066EA');
		$this->addSql('ALTER TABLE usuario_permissoes_permissao DROP FOREIGN KEY FK_60C5FE1AE009E574');
		$this->addSql('DROP TABLE permissao');
		$this->addSql('DROP TABLE permissao_permissao');
		$this->addSql('DROP TABLE usuario_permissoes');
		$this->addSql('DROP TABLE usuario_permissoes_permissao');
	}
}
