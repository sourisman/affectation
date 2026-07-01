<?php
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }

$id = trim($_POST['numEmp'] ?? '');
if ($id === '') { header('Location: index.php'); exit; }

try {
    $pdo->prepare("DELETE FROM EMPLOYE WHERE numEmp = ?")->execute([$id]);
    header('Location: index.php?msg=deleted&type=success');
} catch (PDOException $e) {
    header('Location: index.php?msg=used&type=danger');
}
exit;