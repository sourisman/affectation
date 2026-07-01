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
    header('Location: index.php?msg=Champs+manquants&type=danger');
    exit;
}

$stmt = $pdo->prepare("UPDATE LIEU SET design = ?, province = ? WHERE idlieu = ?");
$stmt->execute([$design, $province, $idlieu]);

header('Location: index.php?msg=updated&type=success');
exit;
