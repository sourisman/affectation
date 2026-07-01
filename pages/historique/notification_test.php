<?php
// Test simple - Vérifier que le fichier s'exécute

// CHEMINS ABSOLUS - CORRECTE !
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "✅ Le fichier notification_test.php s'exécute<br>";
echo "Date/Heure: " . date('Y-m-d H:i:s') . "<br>";
echo "Chemin du fichier: " . __DIR__ . "<br>";

// Test 1: Vérifier les includes
echo "<h3>Test 1: Includes</h3>";
if (file_exists(__DIR__ . '/../../config/db.php')) {
    echo "✅ config/db.php existe<br>";
} else {
    echo "❌ config/db.php N'EXISTE PAS<br>";
}

if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    echo "✅ vendor/autoload.php existe<br>";
} else {
    echo "❌ vendor/autoload.php N'EXISTE PAS<br>";
}

// Test 2: Base de données
echo "<h3>Test 2: Base de données</h3>";
try {
    echo "✅ PDO connecté<br>";
    
    // Tester une requête
    $result = $pdo->query("SELECT COUNT(*) as cnt FROM AFFECTER");
    $row = $result->fetch();
    echo "✅ Table AFFECTER: " . $row['cnt'] . " enregistrements<br>";
} catch (Exception $e) {
    echo "❌ Erreur BD: " . $e->getMessage() . "<br>";
}

// Test 3: PHPMailer
echo "<h3>Test 3: PHPMailer</h3>";
try {
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "✅ PHPMailer classe trouvée<br>";
        
        $mail = new PHPMailer(true);
        echo "✅ Instance PHPMailer créée<br>";
    } else {
        echo "❌ Classe PHPMailer introuvable<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur PHPMailer: " . $e->getMessage() . "<br>";
}

// Test 4: GET parameters
echo "<h3>Test 4: GET Parameters</h3>";
if (isset($_GET['numAffect'])) {
    echo "✅ numAffect reçu: " . htmlspecialchars($_GET['numAffect']) . "<br>";
} else {
    echo "❌ numAffect non reçu<br>";
}

// Test 5: Test d'envoi simplifié
echo "<h3>Test 5: Test d'envoi simplifié</h3>";
if (isset($_GET['numAffect']) && !empty($_GET['numAffect'])) {
    try {
        $numAffect = $_GET['numAffect'];
        
        $stmt = $pdo->prepare("
            SELECT a.numAffect, a.numEmp, e.mail, e.nom, e.prenom
            FROM AFFECTER a
            INNER JOIN EMPLOYE e ON a.numEmp = e.numEmp
            WHERE a.numAffect = ?
        ");
        $stmt->execute([$numAffect]);
        $data = $stmt->fetch();
        
        if ($data) {
            echo "✅ Affectation trouvée: " . $data['numAffect'] . "<br>";
            echo "✅ Email: " . htmlspecialchars($data['mail']) . "<br>";
            echo "✅ Nom: " . htmlspecialchars($data['nom'] . ' ' . $data['prenom']) . "<br>";
            
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'mariesarahjessy@gmail.com';
            $mail->Password = 'jwxy rikl rsnq vvzt';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            echo "✅ Config SMTP OK<br>";
            
            $mail->setFrom('mariesarahjessy@gmail.com', 'Test RH');
            $mail->addAddress($data['mail']);
            $mail->isHTML(true);
            $mail->Subject = 'TEST - Notification d\'affectation';
            $mail->Body = '<p>Ceci est un TEST d\'envoi de mail.</p>';
            
            if ($mail->send()) {
                echo "<h2 style='color:green'>✅✅✅ EMAIL ENVOYÉ AVEC SUCCÈS! ✅✅✅</h2><br>";
            } else {
                echo "❌ Erreur envoi: " . $mail->ErrorInfo . "<br>";
            }
            
        } else {
            echo "❌ Affectation non trouvée<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Exception: " . $e->getMessage() . "<br>";
    }
}

echo "<br><hr>";
echo "<a href='notification_test.php?numAffect=1'>Tester avec numAffect=1</a><br>";
echo "Remplacez 1 par un vrai numéro d'affectation.";
?>
