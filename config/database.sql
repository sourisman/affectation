CREATE DATABASE IF NOT EXISTS affectation
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE affectation;

CREATE TABLE IF NOT EXISTS LIEU (
    idlieu      VARCHAR(10)  NOT NULL,
    design      VARCHAR(100) NOT NULL,
    province    VARCHAR(100) NOT NULL,
    CONSTRAINT pk_lieu PRIMARY KEY (idlieu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS EMPLOYE (
    numEmp      VARCHAR(10)  NOT NULL,
    civilite    VARCHAR(10)  NOT NULL,
    nom         VARCHAR(80)  NOT NULL,
    prenom      VARCHAR(80)  NOT NULL,
    mail        VARCHAR(120) NOT NULL,
    poste       VARCHAR(80)  NOT NULL,
    lieu        VARCHAR(10)  NOT NULL,
    CONSTRAINT pk_employe   PRIMARY KEY (numEmp),
    CONSTRAINT fk_emp_lieu  FOREIGN KEY (lieu) REFERENCES LIEU(idlieu)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS AFFECTER (
    numAffect        VARCHAR(15) NOT NULL,
    numEmp           VARCHAR(10) NOT NULL,
    ancienLieu       VARCHAR(10) NOT NULL,
    nouveauLieu      VARCHAR(10) NOT NULL,
    dateAffect       DATE        NOT NULL,
    datePriseService DATE        NOT NULL,
    CONSTRAINT pk_affecter   PRIMARY KEY (numAffect),
    CONSTRAINT fk_aff_emp    FOREIGN KEY (numEmp)      REFERENCES EMPLOYE(numEmp) ON DELETE RESTRICT,
    CONSTRAINT fk_aff_ancien FOREIGN KEY (ancienLieu)  REFERENCES LIEU(idlieu)   ON DELETE RESTRICT,
    CONSTRAINT fk_aff_nvx    FOREIGN KEY (nouveauLieu) REFERENCES LIEU(idlieu)   ON DELETE RESTRICT,
    CONSTRAINT chk_dates     CHECK (datePriseService >= dateAffect),
    CONSTRAINT chk_lieux     CHECK (ancienLieu <> nouveauLieu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
