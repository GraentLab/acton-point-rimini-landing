<?php
/**
 * Copia questo file in smtp-config.php (stessa cartella) e inserisci
 * la password per le app generata su https://myaccount.google.com/apppasswords
 * per l'account comunicazioni@forini.com.
 *
 * smtp-config.php NON va mai committato su git: e' gia' escluso dal
 * .gitignore. In produzione viene creato automaticamente dal workflow
 * di deploy a partire dal secret SMTP_APP_PASSWORD su GitHub.
 */
return [
    'host'     => 'smtp.gmail.com',
    'port'     => 587,
    'username' => 'comunicazioni@forini.com',
    'password' => 'INSERISCI-QUI-APP-PASSWORD',
];
