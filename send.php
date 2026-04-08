





<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// ✅ Vérifie si formulaire envoyé
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit();
}

// ✅ Anti-spam (honeypot)
if (!empty($_POST['website'])) {
    die("🚫 Spam détecté");
}

// ✅ Anti-spam (temps)
if (isset($_SESSION['last_send'])) {
    if (time() - $_SESSION['last_send'] < 10) {
        die("⏳ Attends quelques secondes avant de renvoyer");
    }
}

// ✅ Sécurisation des données
$nom = htmlspecialchars(trim($_POST['nom']));
$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$subject = htmlspecialchars(trim($_POST['subject']));
$message = htmlspecialchars(trim($_POST['message']));

// ✅ Validation simple
if (empty($nom) || empty($email) || empty($message)) {
    die("❌ Champs obligatoires manquants");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("❌ Email invalide");
}

$mail = new PHPMailer(true);

try {
    // CONFIG SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'naboudjat@gmail.com';
    $mail->Password = 'mojgebeuhoppoprv'; // 🔐 mot de passe d'application
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // EXPÉDITEUR
    $mail->setFrom('naboudjat@gmail.com', 'Portfolio Joseph');

    // Répondre au client
    $mail->addReplyTo($email, $nom);

    // DESTINATAIRE
    $mail->addAddress('naboudjat@gmail.com');

    // CONTENU
    $mail->isHTML(false);
    $mail->Subject = $subject ?: "Nouveau message du portfolio";

    $mail->Body = "Nom: $nom\n"
                . "Email: $email\n\n"
                . "Message:\n$message";

    // ENVOI
    $mail->send();

    // Sauvegarde anti-spam timing
    $_SESSION['last_send'] = time();

    // REDIRECTION (IMPORTANT : pas de echo avant)
    header("Location: success.html");
    exit();

} catch (Exception $e) {
    echo "❌ Erreur lors de l'envoi : {$mail->ErrorInfo}";
}
?>