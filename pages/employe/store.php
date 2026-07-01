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

$c = $pdo->prepare("SELECT numEmp FROM EMPLOYE WHERE numEmp = ?");
$c->execute([$numEmp]);
if ($c->fetch()) { header('Location: index.php?msg=exists&type=danger'); exit; }

$pdo->prepare("INSERT INTO EMPLOYE (numEmp,civilite,nom,prenom,mail,poste,lieu) VALUES (?,?,?,?,?,?,?)")
    ->execute([$numEmp, $civilite, $nom, $prenom, $mail, $poste, $lieu]);

header('Location: index.php?msg=created&type=success'); exit;