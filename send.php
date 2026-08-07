<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    header('Location: index.html?status=config');
    exit();
}
require $configPath;

function clean($data) {
    return htmlspecialchars(trim((string) $data), ENT_QUOTES, 'UTF-8');
}

function redirectWithStatus($status) {
    header('Location: index.html?status=' . urlencode($status) . '#contact');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit();
}

if (!empty($_POST['website'])) {
    redirectWithStatus('spam');
}

if (isset($_SESSION['last_send']) && time() - $_SESSION['last_send'] < 15) {
    redirectWithStatus('wait');
}

$nom = clean($_POST['nom'] ?? '');
$email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_SANITIZE_EMAIL);
$numero = clean($_POST['numero'] ?? '');
$subject = clean($_POST['subject'] ?? '');
$message = clean($_POST['message'] ?? '');

if ($nom === '' || $email === '' || $message === '') {
    redirectWithStatus('missing');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithStatus('invalid');
}

if (mb_strlen($nom) > 100 || mb_strlen($subject) > 150 || mb_strlen($message) > 5000) {
    redirectWithStatus('invalid');
}

try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = MAIL_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USER;
    $mail->Password = MAIL_PASS;
    $mail->SMTPSecure = ((int) MAIL_PORT === 465)
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int) MAIL_PORT;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom(MAIL_USER, MAIL_FROM_NAME);
    $mail->addAddress(MAIL_TO);
    $mail->addReplyTo($email, $nom);

    $mail->isHTML(false);
    $mail->Subject = $subject !== '' ? $subject : 'Nouveau message — Portfolio';

    $phoneLine = $numero !== '' ? $numero : 'Non renseigné';
    $mail->Body =
        "Nom: $nom\n" .
        "Email: $email\n" .
        "Téléphone: $phoneLine\n" .
        "Sujet: " . ($subject !== '' ? $subject : 'Sans sujet') . "\n\n" .
        "Message:\n$message";

    $mail->send();

    $_SESSION['last_send'] = time();
    header('Location: success.html');
    exit();
} catch (Exception $e) {
    redirectWithStatus('error');
}
