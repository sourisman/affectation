<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
<body style="font-family: Times New Roman, serif; font-size: 14px; color: #000; max-width: 600px; margin: auto; padding: 20px;">
    <div style="text-align:center; border-bottom: 2px solid #1e3a5f; padding-bottom: 12px; margin-bottom: 20px;">
        <p style="font-size:16px; font-weight:bold; text-transform:uppercase; margin:0">République de Madagascar</p>
        <p style="margin:4px 0; font-size:12px;">Fihaonana - Fandrosoana - Fahafahana</p>
        <p style="margin:4px 0; font-weight:bold;">Organisme / Ministère</p>
        <p style="margin:0; font-size:13px;">Direction des Ressources Humaines</p>
    </div>
    <h2 style="text-align:center; text-transform:uppercase; text-decoration:underline; font-size:16px;">Notification d'Affectation</h2>
    <p style="text-align:center; font-weight:bold;">N° {$data['numAffect']} du {$dateAffect}</p>
    <p style="text-align:justify; line-height:1.8; margin-top:24px;">
        Madame / Monsieur <strong>{$nomComplet}</strong>,
    </p>
    <p style="text-align:justify; line-height:1.8; text-indent:30px;">
        Nous vous informons que vous faites l'objet d'une décision d'affectation.
        En tant que <strong>{$data['poste']}</strong>
        actuellement en poste à <strong>{$data['ancienLieu']}</strong>,
        vous êtes affecté(e) à <strong>{$data['nouveauLieu']}</strong>
        à compter du <strong>{$datePriseService}</strong>.
    </p>
    <p style="text-align:justify; line-height:1.8; text-indent:30px;">
        Nous vous prions de bien vouloir prendre les dispositions nécessaires
        pour rejoindre votre nouveau poste à la date indiquée.
    </p>
    <p style="text-align:justify; line-height:1.8; text-indent:30px;">
        Pour toute information complémentaire, veuillez contacter
        la Direction des Ressources Humaines.
    </p>
    <p style="margin-top:30px;">Antananarivo, le {$dateAffect}</p>
    <div style="text-align:right; margin-top:40px;">
        <p style="font-weight:bold;">Le Directeur des Ressources Humaines</p>
        <br><br>
        <p>................................................</p>
        <p><em>Signature et cachet</em></p>
    </div>
</body>
</html>
HTML;

$mail = new PHPMailer(true);
$success = false;
$error   = '';

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'mariesarahjessy@gmail.com';
    $mail->Password   = 'jwxy rikl rsnq vvzt';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom('mariesarahjessy@gmail.com', 'Direction RH');
    $mail->addAddress($data['mail'], $nomComplet);

    $mail->isHTML(true);
    $mail->Subject = 'Notification d\'affectation N°' . $data['numAffect'];
    $mail->Body    = $corps;
    $mail->AltBody = strip_tags(str_replace(['<br>', '<p>', '</p>'], ["\n", "\n", "\n"], $corps));

    $mail->send();
    $success = true;

} catch (Exception $e) {
    $error = $mail->ErrorInfo ?? $e->getMessage();
}

// Redirection avec numEmp pour rafficher les affectations
$redirect = 'index.php?numEmp=' . urlencode($data['numEmp']);

if ($success) {
    $redirect .= '&msg=mail_envoye&type=success';
} else {
    $redirect .= '&msg=' . urlencode('Erreur mail : ' . $error) . '&type=danger';
}

header('Location: ' . $redirect);
exit;
?>
