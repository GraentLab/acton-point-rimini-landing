/**
 * Foglio condiviso "Landing Acton" - riceve i contatti compilati sulle landing
 * dhune.actonpoint.it e riminicalcio.actonpoint.it.
 *
 * Va incollato in: foglio Google > Estensioni > Apps Script.
 * Istruzioni complete in tools/GOOGLE-SHEET-SETUP.md.
 *
 * Il token NON sta qui: si imposta una volta in
 * Impostazioni progetto > Proprieta' script > SHEET_TOKEN.
 */

var COLONNE = ['Data', 'Landing', 'Nome', 'Cognome', 'Email', 'Telefono', 'Mail inviata', 'Note'];

/** Esegui UNA volta a mano: crea il foglio Contatti con le intestazioni. */
function setup() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var s = ss.getSheetByName('Contatti') || ss.insertSheet('Contatti');
  s.getRange(1, 1, 1, COLONNE.length).setValues([COLONNE]).setFontWeight('bold').setBackground('#e8f0e0');
  s.setFrozenRows(1);
  s.setColumnWidths(1, COLONNE.length, 140);
  // Nome, Cognome, Email, Telefono come testo: niente numeri "mangiati" e niente formule iniettate
  s.getRange('C2:F').setNumberFormat('@');
  s.getRange('A2:A').setNumberFormat('dd/mm/yyyy hh:mm');
}

function doPost(e) {
  var lock = LockService.getScriptLock();
  try {
    var p = JSON.parse(e.postData.contents);
    var token = PropertiesService.getScriptProperties().getProperty('SHEET_TOKEN');
    if (!token || p.token !== token) return risposta_({ ok: false, errore: 'token' });
    if (p.type !== 'lead') return risposta_({ ok: false, errore: 'tipo' });

    lock.waitLock(10000);
    SpreadsheetApp.getActiveSpreadsheet().getSheetByName('Contatti').appendRow([
      new Date(), p.landing || '', p.nome || '', p.cognome || '', p.email || '', p.telefono || '', p.mail || '', ''
    ]);
    return risposta_({ ok: true });
  } catch (err) {
    return risposta_({ ok: false, errore: String(err) });
  } finally {
    try { lock.releaseLock(); } catch (x) {}
  }
}

function risposta_(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj)).setMimeType(ContentService.MimeType.JSON);
}
