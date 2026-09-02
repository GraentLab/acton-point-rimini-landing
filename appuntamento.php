<?php
/**
 * Gestione richieste appuntamento - Acton Point Rimini
 *
 * IMPORTANTE: prima di andare online, imposta l'indirizzo email
 * di destinazione qui sotto ($destinatario) - può essere lo stesso
 * usato in contact.php o uno diverso dedicato agli appuntamenti.
 */

$destinatario = "info@actonpoint.it"; // <-- cambia con l'email reale a cui ricevere le richieste
$redirect_base = "index.html";

function redirect_con_esito($esito) {
    global $redirect_base;
    header("Location: " . $redirect_base . "?appt=" . $esito . "#appuntamento");
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
$note           = isset($_POST['note']) ? trim($_POST['note']) : '';

// Validazione base dei campi obbligatori
if ($nome === '' || $cognome === '' || $email === '' || $telefono === '' || $data_preferita === '' || $fascia_oraria === '') {
    redirect_con_esito('0');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_con_esito('0');
}

// Sanitizzazione minima per l'header email (anti header-injection)
$nome    = str_replace(array("\r", "\n"), '', $nome);
$cognome = str_replace(array("\r", "\n"), '', $cognome);
$email   = str_replace(array("\r", "\n"), '', $email);

$oggetto = "Richiesta appuntamento dal sito Acton Point Rimini";

$corpo  = "Hai ricevuto una nuova richiesta di appuntamento da actonpoint.it\n\n";
$corpo .= "Nome: " . $nome . " " . $cognome . "\n";
$corpo .= "Email: " . $email . "\n";
$corpo .= "Telefono: " . $telefono . "\n";
$corpo .= "Data preferita: " . $data_preferita . "\n";
$corpo .= "Fascia oraria: " . $fascia_oraria . "\n\n";
$corpo .= "Note:\n" . ($note !== '' ? $note : '-') . "\n";

$headers  = "From: Sito Acton Point <no-reply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$inviata = mail($destinatario, $oggetto, $corpo, $headers);

redirect_con_esito($inviata ? '1' : '0');
