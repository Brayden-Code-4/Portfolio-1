













<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// ENV
function env($key) {
    return getenv($key);
}

// VALIDATION
function clean($data) {
    return htmlspecialchars(trim($data));
}

// CHECK POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit();
}

// HONEYPOT
if (!empty($_POST['website'])) {
    die("🚫 Spam détecté");
}

// ANTI FLOOD
if (isset($_SESSION['last_send']) && time() - $_SESSION['last_send'] < 10) {
    die("⏳ Attends avant de renvoyer");
}

// DATA
$nom = clean($_POST['nom']);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$subject = clean($_POST['subject']);
$message = clean($_POST['message']);

// VALIDATION
if (!$nom || !$email || !$message) {
    die("❌ Champs manquants");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("❌ Email invalide");
}

// ENVOI
try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = env('MAIL_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = env('MAIL_USER');
    $mail->Password = env('MAIL_PASS');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = env('MAIL_PORT');

    $mail->setFrom(env('MAIL_USER'), 'Portfolio Joseph');
    $mail->addAddress(env('MAIL_TO'));
    $mail->addReplyTo($email, $nom);

    $mail->isHTML(false);
    $mail->Subject = $subject ?: "Nouveau message";

    $mail->Body = "Nom: $nom\nEmail: $email\n\nMessage:\n$message";

    $mail->send();

    $_SESSION['last_send'] = time();

    header("Location: success.html");
    exit();

} catch (Exception $e) {
    echo "❌ Erreur : " . $mail->ErrorInfo;
}