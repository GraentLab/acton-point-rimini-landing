<?php
/**
 * Invia una riga al foglio Google condiviso (Google Apps Script web app).
 *
 * Configurazione in smtp-config.php (non versionato): 'sheet_url' e 'sheet_token'.
 * Se mancano, non fa nulla: le landing funzionano comunque.
 */

function sheet_log(array $config, array $payload) {
    $url   = isset($config['sheet_url']) ? $config['sheet_url'] : '';
    $token = isset($config['sheet_token']) ? $config['sheet_token'] : '';
    if ($url === '' || $token === '' || !function_exists('curl_init')) {
        return false;
    }

    $payload['token'] = $token;

    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => array('Content-Type: text/plain;charset=utf-8'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 6,
    ));
    $res = curl_exec($ch);
    $ok  = $res !== false && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
    curl_close($ch);
    return $ok;
}
