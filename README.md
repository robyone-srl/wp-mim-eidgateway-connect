# Login con eID-Gateway

Questa repository contiene il codice e gli asset per la [directory dei plugin di WordPress](https://it.wordpress.org/plugins/) del plugin "Login con eID-Gateway", che permette di effettuare il login ai siti di WordPress con [eID-Gateway](https://www.istruzione.it/spid-cie/), il sistema che si interfaccia a SPID, CIE e eIDAS messo a disposizione dal Ministero dell'Istruzione e del Merito.

Questo plugin non è ufficiale, ma è stato sviluppato da [Robyone S.r.l.](https://robyone.net/) per permettere alle scuole con cui collabora di effettuare il login al sito scolastico con SPID e CIE.

## Installazione
Il plugin non è ancora disponibile direttamente da WordPress, quindi per ora è necessario scaricarlo e installarlo manualmente.

Per ottenere la cartella ZIP contenente il plugin da caricare in WordPress, puoi:
- scaricare la release più recente, oppure
- scaricare la repository e comprimere il contenuto della cartella `trunk` in una cartella compressa chiamata `wp-mim-eidgateway-connect.zip` (solo il contenuto, non la cartella).

## Configurazione e utilizzo
Per configurare il plugin, e per altre informazioni sul processo di aggregazione, puoi consultare il [file readme del plugin](readme.txt).

Per inserire manualmente i pulsanti di login con SPID e CIE, puoi utilizzare lo shortcode `eid_gateway_buttons` (se usi il tema Design Scuole Italia, questa operazione non è necessaria).