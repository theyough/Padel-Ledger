<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260511183500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema for padel level manager';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE player (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, password_hash VARCHAR(255) NOT NULL, first_name VARCHAR(80) NOT NULL, last_name VARCHAR(80) NOT NULL, level INT NOT NULL, rating DOUBLE PRECISION NOT NULL, match_count INT NOT NULL, questionnaire_answers JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_player_email ON player (email)');

        $this->addSql('CREATE TABLE padel_match (id SERIAL NOT NULL, team_a_player1_id INT NOT NULL, team_a_player2_id INT NOT NULL, team_b_player1_id INT NOT NULL, team_b_player2_id INT NOT NULL, created_by_id INT NOT NULL, current_score_proposal_id INT DEFAULT NULL, status VARCHAR(40) NOT NULL, scheduled_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, finished_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, validated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_PADEL_MATCH_A1 ON padel_match (team_a_player1_id)');
        $this->addSql('CREATE INDEX IDX_PADEL_MATCH_A2 ON padel_match (team_a_player2_id)');
        $this->addSql('CREATE INDEX IDX_PADEL_MATCH_B1 ON padel_match (team_b_player1_id)');
        $this->addSql('CREATE INDEX IDX_PADEL_MATCH_B2 ON padel_match (team_b_player2_id)');
        $this->addSql('CREATE INDEX IDX_PADEL_MATCH_CREATED_BY ON padel_match (created_by_id)');
        $this->addSql('CREATE INDEX IDX_PADEL_MATCH_CURRENT_SCORE ON padel_match (current_score_proposal_id)');
        $this->addSql('ALTER TABLE padel_match ADD CONSTRAINT FK_PADEL_MATCH_A1 FOREIGN KEY (team_a_player1_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE padel_match ADD CONSTRAINT FK_PADEL_MATCH_A2 FOREIGN KEY (team_a_player2_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE padel_match ADD CONSTRAINT FK_PADEL_MATCH_B1 FOREIGN KEY (team_b_player1_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE padel_match ADD CONSTRAINT FK_PADEL_MATCH_B2 FOREIGN KEY (team_b_player2_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE padel_match ADD CONSTRAINT FK_PADEL_MATCH_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE score_proposal (id SERIAL NOT NULL, padel_match_id INT NOT NULL, proposed_by_id INT NOT NULL, sets JSON NOT NULL, is_current BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_SCORE_PROPOSAL_MATCH ON score_proposal (padel_match_id)');
        $this->addSql('CREATE INDEX IDX_SCORE_PROPOSAL_PLAYER ON score_proposal (proposed_by_id)');
        $this->addSql('ALTER TABLE score_proposal ADD CONSTRAINT FK_SCORE_PROPOSAL_MATCH FOREIGN KEY (padel_match_id) REFERENCES padel_match (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE score_proposal ADD CONSTRAINT FK_SCORE_PROPOSAL_PLAYER FOREIGN KEY (proposed_by_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE padel_match ADD CONSTRAINT FK_PADEL_MATCH_CURRENT_SCORE FOREIGN KEY (current_score_proposal_id) REFERENCES score_proposal (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE score_validation (id SERIAL NOT NULL, score_proposal_id INT NOT NULL, player_id INT NOT NULL, decision VARCHAR(20) NOT NULL, comment TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_score_validation_proposal_player ON score_validation (score_proposal_id, player_id)');
        $this->addSql('CREATE INDEX IDX_SCORE_VALIDATION_PROPOSAL ON score_validation (score_proposal_id)');
        $this->addSql('CREATE INDEX IDX_SCORE_VALIDATION_PLAYER ON score_validation (player_id)');
        $this->addSql('ALTER TABLE score_validation ADD CONSTRAINT FK_SCORE_VALIDATION_PROPOSAL FOREIGN KEY (score_proposal_id) REFERENCES score_proposal (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE score_validation ADD CONSTRAINT FK_SCORE_VALIDATION_PLAYER FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE score_validation DROP CONSTRAINT FK_SCORE_VALIDATION_PROPOSAL');
        $this->addSql('ALTER TABLE score_validation DROP CONSTRAINT FK_SCORE_VALIDATION_PLAYER');
        $this->addSql('ALTER TABLE padel_match DROP CONSTRAINT FK_PADEL_MATCH_CURRENT_SCORE');
        $this->addSql('ALTER TABLE score_proposal DROP CONSTRAINT FK_SCORE_PROPOSAL_MATCH');
        $this->addSql('ALTER TABLE score_proposal DROP CONSTRAINT FK_SCORE_PROPOSAL_PLAYER');
        $this->addSql('ALTER TABLE padel_match DROP CONSTRAINT FK_PADEL_MATCH_A1');
        $this->addSql('ALTER TABLE padel_match DROP CONSTRAINT FK_PADEL_MATCH_A2');
        $this->addSql('ALTER TABLE padel_match DROP CONSTRAINT FK_PADEL_MATCH_B1');
        $this->addSql('ALTER TABLE padel_match DROP CONSTRAINT FK_PADEL_MATCH_B2');
        $this->addSql('ALTER TABLE padel_match DROP CONSTRAINT FK_PADEL_MATCH_CREATED_BY');
        $this->addSql('DROP TABLE score_validation');
        $this->addSql('DROP TABLE score_proposal');
        $this->addSql('DROP TABLE padel_match');
        $this->addSql('DROP TABLE player');
    }
}
