=== Login con eID-Gateway ===
Contributors: robyone-srl
Tags: spid,cie,mim,scuola,eid
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 8.0
Stable tag: trunk
License: GPL v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Accesso al sito scolastico con eID-Gateway (SPID, CIE e eIDAS)

== Description ==
Questo plugin non ufficiale facilita il processo di integrazione del sito web scolastico realizzato con WordPress al componente eID-Gateway messo a disposizione dal Ministero dell'Istruzione e del Merito (MIM). In questo modo, gli utenti del sito per cui è stato impostato un codice fiscale riusciranno ad accedere con SPID, CIE o eIDAS.

Per utilizzare il plugin è necessario che la scuola abbia aderito al [Gateway delle Identità](https://www.istruzione.it/spid-cie/scuole.html) del Progetto SIIS e che abbia effettuato l'aggregazione (Applicazioni SIDI > PNRR - PA Digitale > Gestione aggregazione scuola).

[Descrivere come fare]

Una volta registrato il servizio, si ottiene un Client ID, da utilizzare della configurazione del plugin.

# Registrazione dei codici fiscali

Per ogni utente che desideri possa accedere con eID-Gateway, devi registrare il suo codice fiscale.

Solo gli utenti amministratori che hanno il permesso di modificare gli utenti ("edit_user") possono modificare i codici fiscali.

1. Accedi all'elenco degli utenti attraverso il menù laterale alla voce "Utenti" oppure "Utenti/Persone".
2. Apri la pagina del profilo dell'utente interessato.
3. Inserisci il suo codice fiscale nel relativo campo.
4. Salva.
5. Ripeti dal punto 2 per ogni utente interessato.

In alternativa, si possono usare plugin di importazione massiva. In questo caso, il _meta_field_ che contiene il codice fiscale è `codice_fiscale`.

# Configura il plugin

Una volta inseriti i codici fiscali, configura il plugin:

1. Apri la pagina delle impostazioni del plugin (tramite il collegamento "Impostazioni" sotto al nome del plugin nell'elenco dei plugin, o tramite la voce del menù laterale _"Impostazioni" > "eID-Gateway"_).
2. Inserisci il client ID (ottenuto durante la registrazione del servizio nel portale SIDI) e il codice meccanografico della scuola nei relativi campi.
3. Abilita la spunta su "Abilita login con eID-Gateway".
4. Salva con l'apposito pulsante.

Ora, gli utenti per cui è stato impostato il codice fiscale riusciranno a effettuare l'accesso con SPID, CIE o eIDAS. I pulsanti "Entra con SPID" e "Entra con CIE" appariranno sotto al form di login di WordPress.

== Installation ==
[todo]

== Frequently Asked Questions ==
[todo]