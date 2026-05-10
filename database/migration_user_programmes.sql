-- Programmes créés par les visiteurs (front) : jeton de session dans utilisateur_token.
-- Les lignes du catalogue (admin / seed) restent avec utilisateur_token NULL.

ALTER TABLE programmes
    ADD COLUMN utilisateur_token VARCHAR(64) NULL DEFAULT NULL
        COMMENT 'Jeton session : NULL = programme catalogue public'
        AFTER type_programme;

CREATE INDEX idx_programmes_user_token ON programmes (utilisateur_token);
