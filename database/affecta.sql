-- ============================================================================
--  AFFECTA — schéma MySQL et données
--  Généré le 2026-09-24 16:34:35 par bin/dump-sql.php
--  Compatible MySQL 8 / MariaDB 10.6+ (utf8mb4_unicode_ci).
--
--  Comptes de démonstration (à supprimer en production) :
--    admin@affecta.dev    / Admin@2026
--    manager@affecta.dev  / Manager@2026
--
--  Import : mysql -u utilisateur -p base < database/affecta.sql
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
--  Structure
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, name VARCHAR(120) NOT NULL, email VARCHAR(190) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(20) NOT NULL DEFAULT 'admin', job_title VARCHAR(120) NULL, avatar_path VARCHAR(255) NULL, is_active TINYINT(1) NOT NULL DEFAULT 1, last_login_at TIMESTAMP NULL DEFAULT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE UNIQUE INDEX users_email_unique ON users (email);
CREATE INDEX users_role_index ON users (role);
CREATE TABLE IF NOT EXISTS password_resets (email VARCHAR(190) NOT NULL, token VARCHAR(255) NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX password_resets_email_index ON password_resets (email);
CREATE TABLE IF NOT EXISTS login_attempts (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, email VARCHAR(190) NOT NULL, ip VARCHAR(45) NOT NULL, successful TINYINT(1) NOT NULL DEFAULT 0, attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX login_attempts_email_ip_index ON login_attempts (email, ip);
CREATE TABLE IF NOT EXISTS settings (setting_key VARCHAR(100) NOT NULL, setting_value TEXT NULL, group_name VARCHAR(50) NOT NULL DEFAULT 'general', created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (setting_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS contact_messages (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, name VARCHAR(120) NOT NULL, email VARCHAR(190) NOT NULL, phone VARCHAR(40) NULL, subject VARCHAR(160) NOT NULL, message TEXT NOT NULL, status VARCHAR(20) NOT NULL DEFAULT 'new', ip VARCHAR(45) NULL, user_agent VARCHAR(255) NULL, read_at TIMESTAMP NULL DEFAULT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX contact_messages_status_index ON contact_messages (status);
CREATE INDEX contact_messages_created_index ON contact_messages (created_at);
CREATE TABLE IF NOT EXISTS activity_logs (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NULL, action VARCHAR(60) NOT NULL, entity VARCHAR(60) NULL, entity_id VARCHAR(40) NULL, description TEXT NULL, meta JSON NULL, ip VARCHAR(45) NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX activity_logs_entity_index ON activity_logs (entity, entity_id);
CREATE INDEX activity_logs_created_index ON activity_logs (created_at);
CREATE TABLE IF NOT EXISTS lieux (idlieu VARCHAR(10) NOT NULL, design VARCHAR(120) NOT NULL, province VARCHAR(100) NOT NULL, code_analytique VARCHAR(20) NULL, capacite INTEGER NULL, is_active TINYINT(1) NOT NULL DEFAULT 1, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (idlieu)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX lieux_province_index ON lieux (province);
CREATE INDEX lieux_design_index ON lieux (design);
CREATE TABLE IF NOT EXISTS employes (numEmp VARCHAR(10) NOT NULL, civilite VARCHAR(10) NOT NULL, nom VARCHAR(80) NOT NULL, prenom VARCHAR(80) NOT NULL, mail VARCHAR(150) NOT NULL, telephone VARCHAR(30) NULL, poste VARCHAR(100) NOT NULL, lieu VARCHAR(10) NOT NULL, date_embauche DATE NULL, is_active TINYINT(1) NOT NULL DEFAULT 1, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (numEmp), FOREIGN KEY (lieu) REFERENCES lieux(idlieu) ON DELETE RESTRICT ON UPDATE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX employes_mail_index ON employes (mail);
CREATE INDEX employes_lieu_index ON employes (lieu);
CREATE INDEX employes_nom_index ON employes (nom, prenom);
CREATE TABLE IF NOT EXISTS affectations (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, numAffect VARCHAR(20) NOT NULL, numEmp VARCHAR(10) NOT NULL, ancienLieu VARCHAR(10) NOT NULL, nouveauLieu VARCHAR(10) NOT NULL, dateAffect DATE NOT NULL, datePriseService DATE NOT NULL, motif VARCHAR(60) NULL, observation TEXT NULL, statut VARCHAR(20) NOT NULL DEFAULT 'planifie', created_by BIGINT UNSIGNED NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (numEmp) REFERENCES employes(numEmp) ON DELETE RESTRICT ON UPDATE CASCADE, FOREIGN KEY (ancienLieu) REFERENCES lieux(idlieu) ON DELETE RESTRICT ON UPDATE CASCADE, FOREIGN KEY (nouveauLieu) REFERENCES lieux(idlieu) ON DELETE RESTRICT ON UPDATE CASCADE, FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE, CONSTRAINT chk_affectation_dates CHECK (datePriseService >= dateAffect), CONSTRAINT chk_affectation_lieux CHECK (ancienLieu <> nouveauLieu)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE UNIQUE INDEX affectations_num_affect_unique ON affectations (numAffect);
CREATE INDEX affectations_employe_index ON affectations (numEmp);
CREATE INDEX affectations_statut_index ON affectations (statut);
CREATE INDEX affectations_date_index ON affectations (dateAffect);

-- ----------------------------------------------------------------------------
--  Données
-- ----------------------------------------------------------------------------
DELETE FROM `activity_logs`;
DELETE FROM `contact_messages`;
DELETE FROM `affectations`;
DELETE FROM `employes`;
DELETE FROM `lieux`;
DELETE FROM `settings`;
DELETE FROM `users`;

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `job_title`, `avatar_path`, `is_active`, `last_login_at`, `created_at`, `updated_at`) VALUES
  (1, 'Nadia Rakoto', 'admin@affecta.dev', '$2y$10$JjtoBwqhciA2qfPVthqY7evax5t./J6pZvqcpbJxJOWFnpH.VdOy2', 'admin', 'Directrice des opérations', NULL, 1, NULL, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (2, 'Hery Andrianina', 'manager@affecta.dev', '$2y$10$t22zLUa8iNFToELkxvN25unWVNw830ESkX/GipdhmSN1gAsGE622y', 'manager', 'Responsable mobilité interne', NULL, 1, NULL, '2026-09-24 16:34:14', '2026-09-24 16:34:14');

INSERT INTO `settings` (`setting_key`, `setting_value`, `group_name`, `created_at`, `updated_at`) VALUES
  ('site_name', 'AFFECTA', 'general', '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('site_tagline', 'Construisons le futur du digital.', 'general', '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('site_email', 'contact@affecta.dev', 'general', '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('site_phone', '+261 34 12 345 67', 'general', '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('site_address', 'Immeuble Horizon, Ankorondrano, Antananarivo 101', 'general', '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('seo_title', 'AFFECTA — Plateforme de pilotage des affectations', 'seo', '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('seo_description', 'Centralisez les agents, les lieux et les affectations : décisions rapides, données fiables, mobilité interne maîtrisée.', 'seo', '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('seo_keywords', 'affectations, mobilité interne, gestion des effectifs, Madagascar', 'seo', '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('contact_notice', 'Vos données ne sont utilisées que pour répondre à votre demande.', 'contact', '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('contact_auto_reply', '1', 'contact', '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('feature_maintenance', '0', 'features', '2026-09-24 16:34:14', '2026-09-24 16:34:14');

INSERT INTO `lieux` (`idlieu`, `design`, `province`, `code_analytique`, `capacite`, `is_active`, `created_at`, `updated_at`) VALUES
  ('LIEU-001', 'Siège Antananarivo', 'Antananarivo', 'TNR-HQ', 120, 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('LIEU-002', 'Antenne Toamasina', 'Toamasina', 'TMM-01', 45, 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('LIEU-003', 'Antenne Mahajanga', 'Mahajanga', 'MJG-01', 38, 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('LIEU-004', 'Antenne Fianarantsoa', 'Fianarantsoa', 'FIA-01', 32, 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('LIEU-005', 'Antenne Toliara', 'Toliara', 'TLE-01', 25, 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('LIEU-006', 'Centre Antsirabe', 'Antananarivo', 'ATB-01', 28, 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('LIEU-007', 'Hub Antsiranana', 'Antsiranana', 'DIE-01', 22, 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14');

INSERT INTO `employes` (`numEmp`, `civilite`, `nom`, `prenom`, `mail`, `telephone`, `poste`, `lieu`, `date_embauche`, `is_active`, `created_at`, `updated_at`) VALUES
  ('EMP-0001', 'M.', 'Razanamalala', 'Nomena', 'nomena.razanamalala1@affecta.dev', '+261 33 82 126 65', 'Comptable', 'LIEU-001', '2020-05-04', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0002', 'M.', 'Andriatsitohaina', 'Toky', 'toky.andriatsitohaina2@affecta.dev', '+261 32 91 871 12', 'Contrôleur qualité', 'LIEU-005', '2021-06-13', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0003', 'Mme', 'Ramanana', 'Ony', 'ony.ramanana3@affecta.dev', '+261 33 63 790 53', 'Agent administratif', 'LIEU-003', '2023-08-07', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0004', 'M.', 'Randrianasolo', 'Fara', 'fara.randrianasolo4@affecta.dev', '+261 34 16 852 36', 'Comptable', 'LIEU-007', '2026-05-30', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0005', 'M.', 'Randrianasolo', 'Lalaina', 'lalaina.randrianasolo5@affecta.dev', '+261 33 59 498 40', 'Responsable RH', 'LIEU-006', '2019-11-08', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0006', 'Mme', 'Andriatsitohaina', 'Aina', 'aina.andriatsitohaina6@affecta.dev', '+261 33 13 937 66', 'Auditeur interne', 'LIEU-005', '2023-12-19', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0007', 'Mlle', 'Andriatsitohaina', 'Herinjaka', 'herinjaka.andriatsitohaina7@affecta.dev', '+261 34 32 815 72', 'Comptable', 'LIEU-005', '2023-05-28', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0008', 'M.', 'Rasoanaivo', 'Miora', 'miora.rasoanaivo8@affecta.dev', '+261 33 97 298 66', 'Responsable logistique', 'LIEU-006', '2018-12-03', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0009', 'Mme', 'Ravaka', 'Lalaina', 'lalaina.ravaka9@affecta.dev', '+261 33 42 801 51', 'Responsable RH', 'LIEU-007', '2023-08-04', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0010', 'M.', 'Ramanana', 'Tahina', 'tahina.ramanana10@affecta.dev', '+261 33 64 907 94', 'Responsable logistique', 'LIEU-003', '2026-04-30', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0011', 'M.', 'Rakotovao', 'Sanda', 'sanda.rakotovao11@affecta.dev', '+261 33 22 612 12', 'Contrôleur qualité', 'LIEU-001', '2021-09-11', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0012', 'Mme', 'Razafy', 'Aina', 'aina.razafy12@affecta.dev', '+261 33 26 878 15', 'Développeur web', 'LIEU-002', '2019-11-27', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0013', 'M.', 'Randrianasolo', 'Volana', 'volana.randrianasolo13@affecta.dev', '+261 32 23 412 70', 'Data analyst', 'LIEU-003', '2021-08-19', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0014', 'Mlle', 'Rabemananjara', 'Soa', 'soa.rabemananjara14@affecta.dev', '+261 32 85 826 92', 'Responsable RH', 'LIEU-003', '2025-10-16', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0015', 'Mme', 'Rabemananjara', 'Herinjaka', 'herinjaka.rabemananjara15@affecta.dev', '+261 33 57 552 20', 'Responsable logistique', 'LIEU-004', '2019-02-08', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0016', 'M.', 'Rakotondrabe', 'Tahina', 'tahina.rakotondrabe16@affecta.dev', '+261 32 22 534 16', 'Responsable RH', 'LIEU-004', '2026-02-03', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0017', 'M.', 'Raharison', 'Vatosoa', 'vatosoa.raharison17@affecta.dev', '+261 34 31 277 54', 'Responsable RH', 'LIEU-006', '2021-05-10', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0018', 'Mme', 'Rakotovao', 'Fenosoa', 'fenosoa.rakotovao18@affecta.dev', '+261 34 16 971 63', 'Agent administratif', 'LIEU-003', '2023-05-21', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0019', 'M.', 'Rabemananjara', 'Andry', 'andry.rabemananjara19@affecta.dev', '+261 34 88 822 92', 'Agent administratif', 'LIEU-001', '2025-07-13', 0, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0020', 'M.', 'Rasoanaivo', 'Mahefa', 'mahefa.rasoanaivo20@affecta.dev', '+261 34 79 433 89', 'Chargé de clientèle', 'LIEU-002', '2019-09-14', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0021', 'Mme', 'Randrianasolo', 'Ony', 'ony.randrianasolo21@affecta.dev', '+261 33 91 722 51', 'Analyste financier', 'LIEU-001', '2024-10-18', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0022', 'M.', 'Rakotovao', 'Mahefa', 'mahefa.rakotovao22@affecta.dev', '+261 32 36 109 54', 'Chargé de clientèle', 'LIEU-005', '2021-03-05', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0023', 'M.', 'Razanamalala', 'Fara', 'fara.razanamalala23@affecta.dev', '+261 32 48 679 29', 'Assistant de direction', 'LIEU-006', '2018-09-21', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0024', 'Mme', 'Andriatsitohaina', 'Volana', 'volana.andriatsitohaina24@affecta.dev', '+261 33 23 310 62', 'Auditeur interne', 'LIEU-004', '2025-04-10', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0025', 'M.', 'Razafy', 'Rado', 'rado.razafy25@affecta.dev', '+261 32 20 455 78', 'Contrôleur qualité', 'LIEU-003', '2022-06-24', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0026', 'M.', 'Raharison', 'Lalaina', 'lalaina.raharison26@affecta.dev', '+261 33 35 984 67', 'Analyste financier', 'LIEU-003', '2021-05-06', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0027', 'Mme', 'Ramanana', 'Aina', 'aina.ramanana27@affecta.dev', '+261 33 36 806 68', 'Analyste financier', 'LIEU-003', '2024-05-24', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0028', 'Mlle', 'Rasoanaivo', 'Miora', 'miora.rasoanaivo28@affecta.dev', '+261 32 68 704 40', 'Comptable', 'LIEU-004', '2026-02-16', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0029', 'M.', 'Raharison', 'Tsiory', 'tsiory.raharison29@affecta.dev', '+261 32 22 835 57', 'Auditeur interne', 'LIEU-003', '2019-05-22', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0030', 'Mme', 'Ravaka', 'Mickaël', 'mickaël.ravaka30@affecta.dev', '+261 34 16 443 11', 'Comptable', 'LIEU-003', '2024-01-20', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0031', 'M.', 'Ratsimba', 'Aina', 'aina.ratsimba31@affecta.dev', '+261 34 41 308 77', 'Développeur web', 'LIEU-007', '2023-06-05', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0032', 'M.', 'Andriatsitohaina', 'Herinjaka', 'herinjaka.andriatsitohaina32@affecta.dev', '+261 32 87 627 80', 'Analyste financier', 'LIEU-001', '2025-04-24', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0033', 'Mme', 'Ratsimba', 'Sitraka', 'sitraka.ratsimba33@affecta.dev', '+261 32 36 318 61', 'Comptable', 'LIEU-007', '2024-05-02', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0034', 'M.', 'Andriamana', 'Hasina', 'hasina.andriamana34@affecta.dev', '+261 33 40 350 20', 'Analyste financier', 'LIEU-003', '2024-05-26', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0035', 'Mlle', 'Rakotovao', 'Fara', 'fara.rakotovao35@affecta.dev', '+261 34 30 353 82', 'Agent administratif', 'LIEU-001', '2020-07-05', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0036', 'Mme', 'Rakotovao', 'Rija', 'rija.rakotovao36@affecta.dev', '+261 33 25 605 18', 'Responsable logistique', 'LIEU-007', '2025-08-23', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0037', 'M.', 'Ravaka', 'Tsiory', 'tsiory.ravaka37@affecta.dev', '+261 32 31 280 97', 'Auditeur interne', 'LIEU-006', '2019-02-11', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0038', 'M.', 'Andriamana', 'Fenosoa', 'fenosoa.andriamana38@affecta.dev', '+261 33 70 492 22', 'Chef de projet', 'LIEU-001', '2020-02-23', 0, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0039', 'Mme', 'Rasoa', 'Tahina', 'tahina.rasoa39@affecta.dev', '+261 33 32 582 34', 'Comptable', 'LIEU-006', '2021-01-27', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0040', 'M.', 'Ravaka', 'Tahina', 'tahina.ravaka40@affecta.dev', '+261 33 30 735 60', 'Assistant de direction', 'LIEU-003', '2024-10-15', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14');
INSERT INTO `employes` (`numEmp`, `civilite`, `nom`, `prenom`, `mail`, `telephone`, `poste`, `lieu`, `date_embauche`, `is_active`, `created_at`, `updated_at`) VALUES
  ('EMP-0041', 'M.', 'Rakotondrabe', 'Herinjaka', 'herinjaka.rakotondrabe41@affecta.dev', '+261 32 64 976 86', 'Ingénieur logiciel', 'LIEU-002', '2020-05-31', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  ('EMP-0042', 'Mme', 'Razafy', 'Rasoa', 'rasoa.razafy42@affecta.dev', '+261 34 73 156 31', 'Responsable RH', 'LIEU-007', '2023-07-05', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14');

INSERT INTO `affectations` (`id`, `numAffect`, `numEmp`, `ancienLieu`, `nouveauLieu`, `dateAffect`, `datePriseService`, `motif`, `observation`, `statut`, `created_by`, `created_at`, `updated_at`) VALUES
  (1, 'AFF-2024-0001', 'EMP-0001', 'LIEU-002', 'LIEU-001', '2024-12-15', '2024-12-31', 'promotion', 'Dossier validé par la direction des ressources humaines.', 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (2, 'AFF-2024-0002', 'EMP-0002', 'LIEU-004', 'LIEU-005', '2024-12-12', '2025-01-16', 'reorganisation', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (3, 'AFF-2025-0003', 'EMP-0004', 'LIEU-002', 'LIEU-007', '2025-07-22', '2025-08-14', 'affectation_initiale', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (4, 'AFF-2026-0004', 'EMP-0005', 'LIEU-001', 'LIEU-006', '2026-04-06', '2026-04-23', 'besoin_service', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (5, 'AFF-2026-0005', 'EMP-0007', 'LIEU-003', 'LIEU-005', '2026-04-04', '2026-04-22', 'promotion', 'Dossier validé par la direction des ressources humaines.', 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (6, 'AFF-2025-0006', 'EMP-0008', 'LIEU-004', 'LIEU-006', '2025-02-27', '2025-03-22', 'promotion', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (7, 'AFF-2025-0007', 'EMP-0010', 'LIEU-001', 'LIEU-003', '2025-01-23', '2025-02-27', 'demande_agent', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (8, 'AFF-2026-0008', 'EMP-0011', 'LIEU-002', 'LIEU-001', '2026-05-01', '2026-06-06', 'demande_agent', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (9, 'AFF-2025-0009', 'EMP-0013', 'LIEU-004', 'LIEU-003', '2025-02-21', '2025-03-06', 'mutation', 'Dossier validé par la direction des ressources humaines.', 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (10, 'AFF-2026-0010', 'EMP-0014', 'LIEU-002', 'LIEU-003', '2026-03-06', '2026-03-24', 'demande_agent', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (11, 'AFF-2025-0011', 'EMP-0016', 'LIEU-002', 'LIEU-004', '2025-02-14', '2025-03-25', 'affectation_initiale', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (12, 'AFF-2025-0012', 'EMP-0017', 'LIEU-006', 'LIEU-005', '2025-07-21', '2025-08-11', 'affectation_initiale', NULL, 'annule', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (13, 'AFF-2025-0013', 'EMP-0019', 'LIEU-003', 'LIEU-001', '2025-03-08', '2025-04-16', 'besoin_service', 'Dossier validé par la direction des ressources humaines.', 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (14, 'AFF-2025-0014', 'EMP-0020', 'LIEU-004', 'LIEU-002', '2025-08-23', '2025-10-03', 'reorganisation', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (15, 'AFF-2025-0015', 'EMP-0022', 'LIEU-007', 'LIEU-005', '2025-04-22', '2025-05-10', 'mutation', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (16, 'AFF-2025-0016', 'EMP-0023', 'LIEU-005', 'LIEU-006', '2025-01-10', '2025-02-17', 'affectation_initiale', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (17, 'AFF-2025-0017', 'EMP-0025', 'LIEU-004', 'LIEU-003', '2025-04-22', '2025-05-27', 'promotion', 'Dossier validé par la direction des ressources humaines.', 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (18, 'AFF-2024-0018', 'EMP-0026', 'LIEU-007', 'LIEU-003', '2024-12-23', '2025-01-21', 'besoin_service', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (19, 'AFF-2026-0019', 'EMP-0028', 'LIEU-003', 'LIEU-004', '2026-04-28', '2026-06-06', 'demande_agent', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (20, 'AFF-2026-0020', 'EMP-0029', 'LIEU-006', 'LIEU-003', '2026-06-16', '2026-07-27', 'promotion', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (21, 'AFF-2024-0021', 'EMP-0031', 'LIEU-005', 'LIEU-007', '2024-11-25', '2024-12-12', 'promotion', 'Dossier validé par la direction des ressources humaines.', 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (22, 'AFF-2026-0022', 'EMP-0032', 'LIEU-005', 'LIEU-001', '2026-08-05', '2026-08-31', 'besoin_service', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (23, 'AFF-2025-0023', 'EMP-0034', 'LIEU-003', 'LIEU-001', '2025-11-02', '2025-11-18', 'mutation', NULL, 'annule', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (24, 'AFF-2025-0024', 'EMP-0035', 'LIEU-003', 'LIEU-001', '2025-12-02', '2026-01-05', 'promotion', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (25, 'AFF-2025-0025', 'EMP-0037', 'LIEU-007', 'LIEU-006', '2025-02-27', '2025-03-25', 'affectation_initiale', 'Dossier validé par la direction des ressources humaines.', 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (26, 'AFF-2025-0026', 'EMP-0038', 'LIEU-006', 'LIEU-001', '2025-08-28', '2025-10-06', 'affectation_initiale', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (27, 'AFF-2024-0027', 'EMP-0040', 'LIEU-001', 'LIEU-003', '2024-10-27', '2024-11-27', 'mutation', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14'),
  (28, 'AFF-2025-0028', 'EMP-0041', 'LIEU-001', 'LIEU-002', '2025-04-07', '2025-05-20', 'reorganisation', NULL, 'applique', 1, '2026-09-24 16:34:14', '2026-09-24 16:34:14');

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `ip`, `user_agent`, `read_at`, `created_at`, `updated_at`) VALUES
  (1, 'Miarisoa Ravelo', 'miarisoa.ravelo@example.mg', '+261 34 12 345 60', 'Refonte du portail interne', 'Bonjour,\nNous souhaitons moderniser notre portail RH interne. Pouvez-vous nous transmettre une estimation pour 250 collaborateurs ?', 'new', '196.192.0.10', 'Seeder/1.0 (démonstration)', NULL, '2026-09-23 09:34:15', '2026-09-23 09:34:15'),
  (2, 'Nicolas Perrin', 'n.perrin@example.fr', '+261 34 12 345 61', 'Démonstration plateforme', 'Bonjour,\nSeriez-vous disponible pour une démonstration la semaine prochaine ? Nous sommes un groupe de 4 filiales.', 'new', '196.192.0.11', 'Seeder/1.0 (démonstration)', NULL, '2026-09-22 02:34:15', '2026-09-22 02:34:15'),
  (3, 'Hanta Ratsima', 'hanta.ratsima@example.mg', '+261 34 12 345 62', 'Interfaçage paie', 'Bonsoir,\nEst-il possible de connecter la plateforme à notre logiciel de paie existant ? Merci.', 'read', '196.192.0.12', 'Seeder/1.0 (démonstration)', NULL, '2026-09-20 19:34:15', '2026-09-20 19:34:15'),
  (4, 'David Osei', 'd.osei@example.com', '+261 34 12 345 63', 'Sécurité des données', 'Hello,\nQuelles certifications couvrez-vous concernant la protection des données personnelles ?', 'read', '196.192.0.13', 'Seeder/1.0 (démonstration)', NULL, '2026-09-19 12:34:15', '2026-09-19 12:34:15'),
  (5, 'Lucas Andry', 'lucas.andry@example.mg', '+261 34 12 345 64', 'Devis maintenance annuelle', 'Bonjour,\nPouvez-vous nous envoyer un devis pour un contrat de maintenance annuelle avec SLA 8h ?', 'answered', '196.192.0.14', 'Seeder/1.0 (démonstration)', NULL, '2026-09-18 05:34:15', '2026-09-18 05:34:15');

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `entity`, `entity_id`, `description`, `meta`, `ip`, `created_at`) VALUES
  (1, 1, 'auth.login', 'user', '2', 'Connexion réussie au tableau de bord', NULL, '196.192.0.5', '2026-09-12 04:34:15'),
  (2, 2, 'auth.login', 'user', '28', 'Connexion réussie au tableau de bord', NULL, '196.192.0.48', '2026-09-12 03:34:15'),
  (3, 1, 'employe.create', 'employe', '36', 'Création d\'une fiche employé', NULL, '196.192.0.51', '2026-09-12 07:34:15'),
  (4, 1, 'lieu.update', 'lieu', '24', 'Mise à jour d\'un lieu d\'affectation', NULL, '196.192.0.39', '2026-09-12 02:34:15'),
  (5, 1, 'auth.login', 'user', '5', 'Connexion réussie au tableau de bord', NULL, '196.192.0.14', '2026-09-13 05:34:15'),
  (6, 1, 'auth.login', 'user', '16', 'Connexion réussie au tableau de bord', NULL, '196.192.0.41', '2026-09-13 05:34:15'),
  (7, 2, 'auth.login', 'user', '37', 'Connexion réussie au tableau de bord', NULL, '196.192.0.11', '2026-09-13 09:34:15'),
  (8, 2, 'employe.create', 'employe', '37', 'Création d\'une fiche employé', NULL, '196.192.0.71', '2026-09-13 07:34:15'),
  (9, 2, 'auth.login', 'user', '18', 'Connexion réussie au tableau de bord', NULL, '196.192.0.76', '2026-09-14 04:34:15'),
  (10, 2, 'affectation.apply', 'affectation', '26', 'Affectation appliquée', NULL, '196.192.0.33', '2026-09-14 04:34:15'),
  (11, 2, 'auth.login', 'user', '39', 'Connexion réussie au tableau de bord', NULL, '196.192.0.70', '2026-09-13 23:34:15'),
  (12, 1, 'employe.create', 'employe', '23', 'Création d\'une fiche employé', NULL, '196.192.0.14', '2026-09-14 05:34:15'),
  (13, 1, 'affectation.apply', 'affectation', '24', 'Affectation appliquée', NULL, '196.192.0.43', '2026-09-14 02:34:15'),
  (14, 1, 'affectation.create', 'affectation', '35', 'Nouvelle affectation enregistrée', NULL, '196.192.0.75', '2026-09-15 02:34:15'),
  (15, 1, 'affectation.apply', 'affectation', '33', 'Affectation appliquée', NULL, '196.192.0.12', '2026-09-15 09:34:15'),
  (16, 2, 'auth.login', 'user', '23', 'Connexion réussie au tableau de bord', NULL, '196.192.0.69', '2026-09-15 02:34:15'),
  (17, 2, 'affectation.apply', 'affectation', '3', 'Affectation appliquée', NULL, '196.192.0.72', '2026-09-15 02:34:15'),
  (18, 2, 'auth.login', 'user', '1', 'Connexion réussie au tableau de bord', NULL, '196.192.0.73', '2026-09-15 10:34:15'),
  (19, 2, 'affectation.create', 'affectation', '16', 'Nouvelle affectation enregistrée', NULL, '196.192.0.50', '2026-09-16 08:34:15'),
  (20, 2, 'lieu.update', 'lieu', '12', 'Mise à jour d\'un lieu d\'affectation', NULL, '196.192.0.61', '2026-09-16 05:34:15'),
  (21, 1, 'employe.create', 'employe', '33', 'Création d\'une fiche employé', NULL, '196.192.0.8', '2026-09-17 06:34:15'),
  (22, 2, 'employe.create', 'employe', '19', 'Création d\'une fiche employé', NULL, '196.192.0.77', '2026-09-18 07:34:15'),
  (23, 2, 'affectation.create', 'affectation', '15', 'Nouvelle affectation enregistrée', NULL, '196.192.0.27', '2026-09-18 09:34:15'),
  (24, 1, 'lieu.update', 'lieu', '18', 'Mise à jour d\'un lieu d\'affectation', NULL, '196.192.0.57', '2026-09-18 08:34:15'),
  (25, 2, 'auth.login', 'user', '36', 'Connexion réussie au tableau de bord', NULL, '196.192.0.73', '2026-09-19 10:34:15'),
  (26, 2, 'auth.login', 'user', '37', 'Connexion réussie au tableau de bord', NULL, '196.192.0.12', '2026-09-19 06:34:15'),
  (27, 2, 'auth.login', 'user', '32', 'Connexion réussie au tableau de bord', NULL, '196.192.0.55', '2026-09-19 08:34:15'),
  (28, 2, 'auth.login', 'user', '9', 'Connexion réussie au tableau de bord', NULL, '196.192.0.32', '2026-09-18 23:34:15'),
  (29, 1, 'employe.create', 'employe', '34', 'Création d\'une fiche employé', NULL, '196.192.0.3', '2026-09-20 01:34:15'),
  (30, 2, 'employe.create', 'employe', '25', 'Création d\'une fiche employé', NULL, '196.192.0.50', '2026-09-20 10:34:15'),
  (31, 1, 'lieu.update', 'lieu', '26', 'Mise à jour d\'un lieu d\'affectation', NULL, '196.192.0.40', '2026-09-20 02:34:15'),
  (32, 1, 'affectation.create', 'affectation', '17', 'Nouvelle affectation enregistrée', NULL, '196.192.0.41', '2026-09-20 00:34:15'),
  (33, 1, 'lieu.update', 'lieu', '25', 'Mise à jour d\'un lieu d\'affectation', NULL, '196.192.0.16', '2026-09-20 00:34:15'),
  (34, 1, 'affectation.create', 'affectation', '40', 'Nouvelle affectation enregistrée', NULL, '196.192.0.4', '2026-09-20 23:34:15'),
  (35, 2, 'auth.login', 'user', '18', 'Connexion réussie au tableau de bord', NULL, '196.192.0.75', '2026-09-21 07:34:15'),
  (36, 1, 'employe.create', 'employe', '37', 'Création d\'une fiche employé', NULL, '196.192.0.8', '2026-09-21 02:34:15'),
  (37, 2, 'affectation.create', 'affectation', '34', 'Nouvelle affectation enregistrée', NULL, '196.192.0.60', '2026-09-21 00:34:15'),
  (38, 1, 'affectation.create', 'affectation', '26', 'Nouvelle affectation enregistrée', NULL, '196.192.0.65', '2026-09-21 00:34:15'),
  (39, 1, 'affectation.create', 'affectation', '16', 'Nouvelle affectation enregistrée', NULL, '196.192.0.28', '2026-09-22 10:34:15'),
  (40, 2, 'lieu.update', 'lieu', '23', 'Mise à jour d\'un lieu d\'affectation', NULL, '196.192.0.24', '2026-09-22 05:34:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `entity`, `entity_id`, `description`, `meta`, `ip`, `created_at`) VALUES
  (41, 1, 'affectation.apply', 'affectation', '30', 'Affectation appliquée', NULL, '196.192.0.46', '2026-09-23 08:34:15'),
  (42, 1, 'lieu.update', 'lieu', '19', 'Mise à jour d\'un lieu d\'affectation', NULL, '196.192.0.75', '2026-09-23 04:34:15'),
  (43, 1, 'auth.login', 'user', '13', 'Connexion réussie au tableau de bord', NULL, '196.192.0.51', '2026-09-23 04:34:15'),
  (44, 2, 'employe.create', 'employe', '39', 'Création d\'une fiche employé', NULL, '196.192.0.63', '2026-09-24 03:34:15'),
  (45, 2, 'affectation.apply', 'affectation', '28', 'Affectation appliquée', NULL, '196.192.0.47', '2026-09-23 23:34:15'),
  (46, 1, 'affectation.apply', 'affectation', '32', 'Affectation appliquée', NULL, '196.192.0.75', '2026-09-24 01:34:15'),
  (47, 1, 'affectation.create', 'affectation', '19', 'Nouvelle affectation enregistrée', NULL, '196.192.0.71', '2026-09-24 05:34:15'),
  (48, 1, 'employe.create', 'employe', '25', 'Création d\'une fiche employé', NULL, '196.192.0.8', '2026-09-25 10:34:15'),
  (49, 2, 'affectation.apply', 'affectation', '34', 'Affectation appliquée', NULL, '196.192.0.22', '2026-09-25 10:34:15'),
  (50, 1, 'affectation.create', 'affectation', '23', 'Nouvelle affectation enregistrée', NULL, '196.192.0.67', '2026-09-25 02:34:15');

SET FOREIGN_KEY_CHECKS = 1;

