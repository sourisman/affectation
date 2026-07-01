<?php
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }

$numAffect        = trim($_POST['numAffect']        ?? '');
$nouveauLieu      = trim($_POST['nouveauLieu']      ?? '');
$dateAffect       = trim($_POST['dateAffect']       ?? '');
$datePriseService = trim($_POST['datePriseService'] ?? '');

if (!$numAffect || !$nouveauLieu || !$dateAffect || !$datePriseService) {
    header('Location: index.php?msg=Champs+manquants&type=danger'); exit;
}

if ($datePriseService < $dateAffect) {
    header('Location: index.php?msg=date_error&type=danger'); exit;
}

$pdo->prepare("
    UPDATE AFFECTER
    SET nouveauLieu = ?, dateAffect = ?, datePriseService = ?
    WHERE numAffect = ?
")->execute([$nouveauLieu, $dateAffect, $datePriseService, $numAffect]);

header('Location: index.php?msg=updated&type=success'); exit;