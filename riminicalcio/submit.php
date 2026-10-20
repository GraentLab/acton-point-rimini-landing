<?php
/**
 * Richiesta informazioni tifosi Rimini Calcio - Acton Point Rimini
 *
 * Riusa PHPMailer e smtp-config.php (non versionato) della root del sito.
 */

$destinatario = "comunicazioni@forini.com";

function redirect_con_esito($esito) {
    header("Location: /?sent=" . $esito . "#richiesta");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_con_esito('0');
}

// Honeypot anti-spam: se compilato è un bot
if (!empty($_POST['website'])) {
    redirect_con_esito('1');
}

$nome     = isset($_POST['nome']) ? trim($_POST['nome']) : '';
$cognome  = isset($_POST['cognome']) ? trim($_POST['cognome']) : '';
$email    = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$privacy  = !empty($_POST['privacy']);

if ($nome === '' || $cognome === '' || $email === '' || $telefono === '' || !$privacy) {
    redirect_con_esito('0');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_con_esito('0');
}

$nome     = str_replace(array("\r", "\n"), '', $nome);
$cognome  = str_replace(array("\r", "\n"), '', $cognome);
$email    = str_replace(array("\r", "\n"), '', $email);
$telefono = str_replace(array("\r", "\n"), '', $telefono);

$root = is_dir(__DIR__ . '/../lib/PHPMailer') ? __DIR__ . '/..' : __DIR__;
$files = array(
    $root . '/lib/PHPMailer/src/Exception.php',
    $root . '/lib/PHPMailer/src/PHPMailer.php',
    $root . '/lib/PHPMailer/src/SMTP.php',
    $root . '/smtp-config.php',
);
foreach ($files as $f) {
    if (!file_exists($f)) {
        redirect_con_esito('0');
    }
}

require $files[0];
require $files[1];
require $files[2];

$smtp = require $files[3];

$corpo  = "Nuova richiesta di informazioni dalla landing tifosi Rimini Calcio (riminicalcio.actonpoint.it)\n\n";
$corpo .= "Nome: " . $nome . " " . $cognome . "\n";
$corpo .= "Email: " . $email . "\n";
$corpo .= "Telefono: " . $telefono . "\n\n";
$corpo .= "Consenso privacy e ricontatto: si\n";
$corpo .= "Data richiesta: " . date('d/m/Y H:i') . "\n";

$inviata = false;
$mail = new PHPMailer\PHPMailer\PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = $smtp['host'];
    $mail->Port       = $smtp['port'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtp['username'];
    $mail->Password   = $smtp['password'];
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom($smtp['username'], 'Acton Point Rimini');
    $mail->addAddress($destinatario);
    $mail->addReplyTo($email, $nome . ' ' . $cognome);

    $mail->Subject = "Richiesta info tifosi Rimini Calcio - " . $nome . " " . $cognome;
    $mail->Body    = $corpo;

    $mail->send();
    $inviata = true;
} catch (PHPMailer\PHPMailer\Exception $e) {
    $inviata = false;
}

redirect_con_esito($inviata ? '1' : '0');
