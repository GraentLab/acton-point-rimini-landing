# Foglio condiviso contatti (DHUNE + Rimini Calcio)

Ogni volta che qualcuno compila il modulo su una delle due landing, una riga viene aggiunta al foglio **Contatti**: data, landing (`dhune` / `riminicalcio`), nome, cognome, email, telefono, mail inviata (si/no). La colonna Note e' libera per i colleghi.

La mail a comunicazioni@forini.com continua ad arrivare come prima. Il contatto finisce nel foglio anche se la mail fallisce.

## Configurazione (una volta sola)

1. Crea un foglio Google nuovo (es. "Landing Acton - contatti") e condividilo con i colleghi.
2. Nel foglio: **Estensioni > Apps Script**. Cancella il contenuto e incolla `tools/google-sheet.gs`. Salva.
3. Menu a tendina delle funzioni > **setup** > Esegui (autorizza l'accesso quando richiesto). Crea il foglio Contatti con le intestazioni.
4. **Impostazioni progetto** (ingranaggio) > **Proprieta' script** > Aggiungi: nome `SHEET_TOKEN`, valore una stringa lunga casuale (es. 32 caratteri). Segnatela: serve al punto 6.
5. **Deploy > Nuova distribuzione > tipo: App web**. Esegui come: **Me**. Chi ha accesso: **Chiunque**. Distribuisci e copia l'**URL** (finisce con `/exec`).
6. Su GitHub (repo > Settings > Secrets and variables > Actions) crea due secret:
   - `SHEET_WEBHOOK_URL` = l'URL del punto 5
   - `SHEET_TOKEN` = lo stesso valore del punto 4
7. Fai un nuovo deploy (push su `main`): il workflow scrive i due valori nel file di configurazione sul server.

Senza i secret le landing funzionano come prima, semplicemente il foglio non si compila.

## Se modifichi lo script

Apps Script > Deploy > **Gestisci distribuzioni** > modifica > Nuova versione. L'URL resta lo stesso.
