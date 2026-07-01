<?php
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }

$numEmp   = trim($_POST['numEmp']   ?? '');
$civilite = trim($_POST['civilite'] ?? '');
$nom      = trim($_POST['nom']      ?? '');
$prenom   = trim($_POST['prenom']   ?? '');
$mail     = trim($_POST['mail']     ?? '');
$poste    = trim($_POST['poste']    ?? '');
$lieu     = trim($_POST['lieu']     ?? '');

if (!$numEmp || !$civilite || !$nom || !$prenom || !$mail || !$poste || !$lieu) {
    header('Location: index.php?msg=Champs+manquants&type=danger'); exit;
}

$pdo->prepare("UPDATE EMPLOYE SET civilite=?,nom=?,prenom=?,mail=?,poste=?,lieu=? WHERE numEmp=?")
    ->execute([$civilite, $nom, $prenom, $mail, $poste, $lieu, $numEmp]);

header('Location: index.php?msg=updated&type=success'); exit;