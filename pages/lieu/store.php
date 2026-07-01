<?php
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$idlieu   = trim($_POST['idlieu']   ?? '');
$design   = trim($_POST['design']   ?? '');
$province = trim($_POST['province'] ?? '');

if ($idlieu === '' || $design === '' || $province === '') {
    header('Location: index.php?msg=Tous+les+champs+sont+obligatoires&type=danger');
    exit;
}

$check = $pdo->prepare("SELECT idlieu FROM LIEU WHERE idlieu = ?");
$check->execute([$idlieu]);
if ($check->fetch()) {
    header('Location: index.php?msg=exists&type=danger');
    exit;
}

$stmt = $pdo->prepare("INSERT INTO LIEU (idlieu, design, province) VALUES (?, ?, ?)");
$stmt->execute([$idlieu, $design, $province]);

header('Location: index.php?msg=created&type=success');
exit;
