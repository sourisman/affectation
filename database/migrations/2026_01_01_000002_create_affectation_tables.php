<?php

declare(strict_types=1);

use App\Core\Schema;

/**
 * Domaine métier : lieux, employés, affectations.
 *
 * Règles portées au niveau base :
 *  - une affectation relie un employé, un lieu d'origine et un lieu d'accueil ;
 *  - la date de prise de service ne peut précéder la date d'affectation ;
 *  - les lieux d'origine et d'accueil doivent être distincts ;
 *  - les suppressions sont restreintes tant qu'une affectation référence la ligne.
 */
return static function (Schema $schema): void {
    $schema->create('lieux', [
        $schema->string('idlieu', 10),
        $schema->string('design', 120),
        $schema->string('province', 100),
        $schema->string('code_analytique', 20, true),
        $schema->integer('capacite', true),
        $schema->boolean('is_active', true),
        $schema->timestamps(),
        $schema->primaryKey('idlieu'),
    ]);
    $schema->index('lieux', 'lieux_province_index', ['province']);
    $schema->index('lieux', 'lieux_design_index', ['design']);

    $schema->create('employes', [
        $schema->string('numEmp', 10),
        $schema->string('civilite', 10),
        $schema->string('nom', 80),
        $schema->string('prenom', 80),
        $schema->string('mail', 150),
        $schema->string('telephone', 30, true),
        $schema->string('poste', 100),
        $schema->string('lieu', 10),
        $schema->date('date_embauche', true),
        $schema->boolean('is_active', true),
        $schema->timestamps(),
        $schema->primaryKey('numEmp'),
        $schema->foreignKey('lieu', 'lieux', 'idlieu', 'RESTRICT'),
    ]);
    $schema->index('employes', 'employes_mail_index', ['mail']);
    $schema->index('employes', 'employes_lieu_index', ['lieu']);
    $schema->index('employes', 'employes_nom_index', ['nom', 'prenom']);

    $schema->create('affectations', [
        $schema->id(),
        $schema->string('numAffect', 20),
        $schema->string('numEmp', 10),
        $schema->string('ancienLieu', 10),
        $schema->string('nouveauLieu', 10),
        $schema->date('dateAffect'),
        $schema->date('datePriseService'),
        $schema->string('motif', 60, true),
        $schema->text('observation', true),
        // planifie → applique → (annule)
        $schema->string('statut', 20, false, 'planifie'),
        $schema->foreignId('created_by', true),
        $schema->timestamps(),
        $schema->foreignKey('numEmp', 'employes', 'numEmp', 'RESTRICT'),
        $schema->foreignKey('ancienLieu', 'lieux', 'idlieu', 'RESTRICT'),
        $schema->foreignKey('nouveauLieu', 'lieux', 'idlieu', 'RESTRICT'),
        $schema->foreignKey('created_by', 'users', 'id', 'SET NULL'),
        $schema->check('chk_affectation_dates', 'datePriseService >= dateAffect'),
        $schema->check('chk_affectation_lieux', 'ancienLieu <> nouveauLieu'),
    ]);
    $schema->index('affectations', 'affectations_num_affect_unique', ['numAffect'], true);
    $schema->index('affectations', 'affectations_employe_index', ['numEmp']);
    $schema->index('affectations', 'affectations_statut_index', ['statut']);
    $schema->index('affectations', 'affectations_date_index', ['dateAffect']);
};
