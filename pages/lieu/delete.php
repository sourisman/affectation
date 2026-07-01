<?php
require_once '../../config/db.php';

$id = $_GET['id'] ?? '';
if ($id === '') {
    header('Location: index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM LIEU WHERE idlieu = ?");
    $stmt->execute([$id]);
    header('Location: index.php?msg=deleted&type=success');
} catch (PDOException $e) {
    header('Location: index.php?msg=used&type=danger');
}
exit;
