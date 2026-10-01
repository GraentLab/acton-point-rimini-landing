<?php
/**
 * Gestione invio form contatti - Acton Point Rimini
 *
 * Invia via SMTP autenticato (Gmail) invece della mail() nativa di PHP:
 * senza autenticazione/DKIM, Gmail scarta o mette in spam la posta in
 * arrivo da hosting condiviso. Le credenziali SMTP vivono in
 * smtp-config.php, che non è versionato (vedi smtp-config.example.php).
 */

require __DIR__ . '/lib/PHPMailer/src/Exception.php';
require __DIR__ . '/lib/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/lib/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

$destinatario = "comunicazioni@forini.com";
$redirect_base = "/";

function redirect_con_esito($esito) {
    global $redirect_base;
    header("Location: " . $redirect_base . "?sent=" . $esito . "#contatti");
    exit;
}

// Accetta solo richieste POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_con_esito('0');
}

// Honeypot anti-spam: se questo campo nascosto è compilato, è un bot
if (!empty($_POST['website'])) {
    redirect_con_esito('1'); // finge successo per non dare indizi ai bot
}

$nome           = isset($_POST['nome']) ? trim($_POST['nome']) : '';
$cognome        = isset($_POST['cognome']) ? trim($_POST['cognome']) : '';
$email          = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefono       = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$data_preferita = isset($_POST['data_preferita']) ? trim($_POST['data_preferita']) : '';
$fascia_oraria  = isset($_POST['fascia_oraria']) ? trim($_POST['fascia_oraria']) : '';
$messaggio      = isset($_POST['messaggio']) ? trim($_POST['messaggio']) : '';

// Validazione base dei campi obbligatori
if ($nome === '' || $cognome === '' || $email === '' || $messaggio === '') {
    redirect_con_esito('0');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_con_esito('0');
}

// Sanitizzazione minima (difesa in profondità, PHPMailer valida comunque gli header)
$nome    = str_replace(array("\r", "\n"), '', $nome);
$cognome = str_replace(array("\r", "\n"), '', $cognome);
$email   = str_replace(array("\r", "\n"), '', $email);

$oggetto = "Nuovo contatto dal sito Acton Point Rimini";

$corpo  = "Hai ricevuto un nuovo messaggio dal form di actonpoint.it\n\n";
$corpo .= "Nome: " . $nome . " " . $cognome . "\n";
$corpo .= "Email: " . $email . "\n";
$corpo .= "Telefono: " . ($telefono !== '' ? $telefono : '-') . "\n";
if ($data_preferita !== '' || $fascia_oraria !== '') {
    $corpo .= "Data preferita per appuntamento: " . ($data_preferita !== '' ? $data_preferita : '-') . "\n";
    $corpo .= "Fascia oraria preferita: " . ($fascia_oraria !== '' ? $fascia_oraria : '-') . "\n";
}
$corpo .= "\nMessaggio:\n" . $messaggio . "\n";

$config_path = __DIR__ . '/smtp-config.php';
$inviata = false;

if (file_exists($config_path)) {
    $smtp = require $config_path;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $smtp['host'];
        $mail->Port       = $smtp['port'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp['username'];
        $mail->Password   = $smtp['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($smtp['username'], 'Acton Point Rimini');
        $mail->addAddress($destinatario);
        $mail->addReplyTo($email, $nome . ' ' . $cognome);

        $mail->Subject = $oggetto;
        $mail->Body    = $corpo;

        $mail->send();
        $inviata = true;
    } catch (PHPMailerException $e) {
        $inviata = false;
    }
}

redirect_con_esito($inviata ? '1' : '0');
