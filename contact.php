<?php
/**
 * Gestione invio form contatti - Acton Point Rimini
 *
 * IMPORTANTE: prima di andare online, imposta l'indirizzo email
 * di destinazione qui sotto ($destinatario).
 */

$destinatario = "INSERISCI-QUI@actonpoint.it"; // <-- cambia con l'email reale a cui ricevere i contatti
$redirect_base = "index.html";

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

// Sanitizzazione minima per l'header email (anti header-injection)
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

$headers  = "From: Sito Acton Point <no-reply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$inviata = mail($destinatario, $oggetto, $corpo, $headers);

redirect_con_esito($inviata ? '1' : '0');
