# Login con eID-Gateway

Questa repository contiene il codice e gli asset per la [directory dei plugin di WordPress](https://it.wordpress.org/plugins/) del plugin "Login con eID-Gateway", che permette di effettuare il login ai siti di WordPress con [eID-Gateway](https://www.istruzione.it/spid-cie/), il sistema che si interfaccia a SPID, CIE e eIDAS messo a disposizione dal Ministero dell'Istruzione e del Merito.

Questo plugin non è ufficiale: è stato sviluppato da [Robyone S.r.l.](https://robyone.net/) per permettere alle scuole con cui collabora di effettuare il login al sito scolastico con SPID e CIE.

## Installazione

Il plugin non è ancora disponibile direttamente da WordPress, quindi per ora è necessario scaricarlo e installarlo manualmente.

Per ottenere la cartella ZIP contenente il plugin da caricare in WordPress, puoi:
- scaricare la [release più recente](https://github.com/robyone-srl/wp-mim-eidgateway-connect/releases/latest) (scegli il file chiamato `wp-mim-eidgateway-connect.zip`), oppure
- scaricare la repository e comprimere il contenuto della cartella `trunk` in una cartella compressa chiamata `wp-mim-eidgateway-connect.zip` (solo il contenuto, non la cartella).

Per eseguire l'installazione:
1. apri il pannello amministratore;
2. recati nella sezione dei plugin dalla barra a sinistra;
3. premi su "Aggiungi nuovo plugin";
4. premi su "Carica plugin";
5. carica la cartella compressa ottenuta prima, trascinandola nell'area di caricamento o scegliendola manualmente;
6. conferma l'installazione;
7. attiva il plugin;
8. segui i passaggi successivi per la configurazione.

## Utilizzo

Di seguito sono descritti i passaggi per integrare l'accesso tramite eID-Gateway al sito scolastico realizzato in WordPress.

### Aggregazione al Gateway delle Identità

Per utilizzare il plugin, è necessario che la scuola abbia completato con successo la richiesta di aggregazione al [Gateway delle Identità](https://www.istruzione.it/spid-cie/scuole.html). Puoi effettuare questa procedura dal portale SIDI, nella sezione "Gestione aggregazione scuola" (possono accedervi il dirigente scolastico e il DSGA). Puoi trovare guida completa [a questa pagina](https://www.istruzione.it/responsabile-transizione-digitale/scuole.html), sotto la sezione "Manuale utente per l'adesione al Gateway delle Identità".

Sempre in "Gestione aggregazione scuola" del portale SIDI, nella scheda "Gestione client", puoi ottenere il _Client ID_ da utilizzare nelle impostazioni del plugin. Il _redirect url_ da impostare è "https://_indirizzo del sito_/wp-json/eid-gateway/login".

### Registrazione dei codici fiscali

Per ogni utente che desideri possa accedere con eID-Gateway, devi registrare il suo codice fiscale. Se in passato hai usato il plugin [WP Spid Italia](https://it.wordpress.org/plugins/wp-spid-italia/) e hai già registrato i codici fiscali per quel plugin, allora questa operazione non dovrebbe essere necessaria.

Solo gli utenti che hanno il permesso di modificare gli utenti ("edit_users") possono modificare i codici fiscali.

1. Accedi all'elenco degli utenti attraverso il menù laterale alla voce "Utenti" oppure "Utenti/Persone".
2. Apri la pagina del profilo dell'utente interessato.
3. Inserisci il suo codice fiscale nel relativo campo.
4. Salva.
5. Ripeti dal punto 2 per ogni utente interessato.

In alternativa, puoi usare plugin di importazione massiva. In questo caso, il _meta_field_ che contiene il codice fiscale è `codice_fiscale`.

### Configurazione del plugin

Una volta inseriti i codici fiscali, configura il plugin:

1. Apri la pagina delle impostazioni del plugin (tramite il collegamento "Impostazioni" sotto al nome del plugin nell'elenco dei plugin, o tramite la voce del menù laterale _Impostazioni > eID-Gateway_).
2. Inserisci il client ID (ottenuto durante la registrazione del servizio nel portale SIDI) e il codice meccanografico della scuola nei relativi campi.
3. Abilita la spunta su "Abilita login con eID-Gateway".
4. Salva con l'apposito pulsante.

Gli utenti per cui è stato impostato il codice fiscale riusciranno a effettuare l'accesso con SPID, CIE o eIDAS. I pulsanti "Entra con SPID" e "Entra con CIE" appariranno sotto al form di login di WordPress (in `/wp-login.php`).

### Integrazione con il tema

#### Design Scuole Italia

Se utilizzi il tema fornito da AgID per le scuole, [Design Scuole Italia](https://github.com/italia/design-scuole-wordpress-theme), puoi utilizzare le opzioni sotto "Integrazione con il tema Design Scuole Italia" (nella pagina delle impostazioni del plugin) per mostrare i pulsanti di login con SPID e CIE anche nel pannello di login del tema, e per nascondere i campi per il login con email e password.

#### Altri temi

Se utilizzi un altro tema, il plugin mette a disposizione lo shortcode `eid_gateway_buttons` per inserire i pulsanti nel punto desiderato. Lo shortcode supporta i parametri qui riportati.

- `size` – Definisce la dimensione dei pulsanti. Può assumere i valori `"s"`, `"m"`, `"l"`, `"xl"` (default: `"m"`);
- `redirect_to` – Definisce la pagina in cui effettuare il redirect dopo il login. Di default, è la pagina di amministrazione.
