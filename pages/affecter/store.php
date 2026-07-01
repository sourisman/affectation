<?php
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$numAffect       = trim($_POST['numAffect'] ?? '');
$numEmp          = trim($_POST['numEmp'] ?? '');
$nouveauLieu     = trim($_POST['nouveauLieu'] ?? '');
$dateAffect      = trim($_POST['dateAffect'] ?? '');
$datePriseService = trim($_POST['datePriseService'] ?? '');

if (
    $numAffect === '' ||
    $numEmp === '' ||
    $nouveauLieu === '' ||
    $dateAffect === '' ||
    $datePriseService === ''
) {
    header('Location: index.php?msg=Tous+les+champs+sont+obligatoires&type=danger');
    exit;
}

/* Vérifier si le numéro existe déjà */
$check = $pdo->prepare("
    SELECT numAffect
    FROM AFFECTER
    WHERE numAffect = ?
");
$check->execute([$numAffect]);

if ($check->fetch()) {
    header('Location: index.php?msg=exists&type=danger');
    exit;
}

/* Récupérer l'employé */
$emp = $pdo->prepare("
    SELECT lieu
    FROM EMPLOYE
    WHERE numEmp = ?
");
$emp->execute([$numEmp]);

$employe = $emp->fetch(PDO::FETCH_ASSOC);

if (!$employe) {
    header('Location: index.php?msg=Employe+introuvable&type=danger');
    exit;
}

$ancienLieu = $employe['lieu'];

/* Ancien lieu et nouveau lieu doivent être différents */
if ($ancienLieu === $nouveauLieu) {
    header('Location: index.php?msg=Lieu+identique&type=danger');
    exit;
}

/* Vérification des dates */
if ($datePriseService < $dateAffect) {
    header('Location: index.php?msg=Date+invalide&type=danger');
    exit;
}

/* Enregistrer l'affectation */
$stmt = $pdo->prepare("
    INSERT INTO AFFECTER (
        numAffect,
        numEmp,
        ancienLieu,
        nouveauLieu,
        dateAffect,
        datePriseService
    )
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $numAffect,
    $numEmp,
    $ancienLieu,
    $nouveauLieu,
    $dateAffect,
    $datePriseService
]);

/* Mise à jour du lieu de l'employé */
$update = $pdo->prepare("
    UPDATE EMPLOYE
    SET lieu = ?
    WHERE numEmp = ?
");

$update->execute([
    $nouveauLieu,
    $numEmp
]);

header('Location: index.php?msg=created&type=success');
exit;