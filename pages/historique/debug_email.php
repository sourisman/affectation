<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$numAffect = trim($_GET['numAffect'] ?? '');
if (!$numAffect) die("❌ Numéro d'affectation manquant.");

// Récupérer les données
$stmt = $pdo->prepare("
    SELECT
        a.numAffect, a.numEmp, a.dateAffect, a.datePriseService,
        e.civilite, e.nom, e.prenom, e.poste, e.mail,
        la.design AS ancienLieu, ln.design AS nouveauLieu
    FROM AFFECTER a
    INNER JOIN EMPLOYE e  ON a.numEmp      = e.numEmp
    LEFT  JOIN LIEU    la ON a.ancienLieu  = la.idlieu
    LEFT  JOIN LIEU    ln ON a.nouveauLieu = ln.idlieu
    WHERE a.numAffect = ?
");

$stmt->execute([$numAffect]);
$data = $stmt->fetch();

if (!$data) die("❌ Affectation introuvable.");

echo "✅ Données trouvées<br>";
echo "Email destination: " . htmlspecialchars($data['mail']) . "<br>";
echo "---<br>";

function fdate($d) {
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt ? $dt->format('d/m/Y') : $d;
}

$dateAffect       = fdate($data['dateAffect']);
$datePriseService = fdate($data['datePriseService']);
$nomComplet       = $data['civilite'] . ' ' . strtoupper($data['nom']) . ' ' . $data['prenom'];

$corps = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"></head>
<body>Test email</body>
</html>
HTML;

$mail = new PHPMailer(true);

try {
    echo "📧 Configuration SMTP...<br>";
    
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'mariesarahjessy@gmail.com';
    $mail->Password   = 'jwxy rikl rsnq vvzt';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';
    
    echo "✅ SMTP configuré<br>";
    
    echo "📧 Test de connexion...<br>";
    if (!$mail->smtpConnect()) {
        throw new Exception('Impossible de se connecter à SMTP');
    }
    echo "✅ Connecté à SMTP<br>";
    
    $mail->setFrom('mariesarahjessy@gmail.com', 'Direction RH');
    $mail->addAddress($data['mail'], $nomComplet);
    $mail->isHTML(true);
    $mail->Subject = 'Notification d\'affectation N°' . $data['numAffect'];
    $mail->Body = $corps;
    
    echo "📧 Envoi du mail...<br>";
    
    if ($mail->send()) {
        echo "<h2 style='color:green'>✅✅✅ EMAIL ENVOYÉ AVEC SUCCÈS! ✅✅✅</h2>";
        echo "📧 Destinataire: " . htmlspecialchars($data['mail']) . "<br>";
    } else {
        echo "❌ Erreur: " . $mail->ErrorInfo;
    }
    
    $mail->smtpClose();
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
    echo "Détails: " . $mail->ErrorInfo . "<br>";
}

echo "<br><a href='javascript:history.back()'>← Retour</a>";
?>
