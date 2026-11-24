# Reserveringen

Plaats de inhoud van `snippets/reserveren.php` zonder de openingsregel `<?php` in Code Snippets en activeer de snippet. Gebruik `[kapper_reserveren]` op een WordPress-pagina. De code maakt de tabel kapper_appointments met WordPress-prefix aan. De combinatie datum/tijd is uniek.

## Nog uitvoeren op de testwebsite
- Geldige afspraak plaatsen en controleren in de database en het dashboard.
- Ongeldig e-mailadres, datum in het verleden en ongeoorloofd tijdslot weigeren.
- Hetzelfde tijdslot tweemaal reserveren; maximaal één aanvraag mag slagen.
- Bevestigingsmail controleren met fictieve testgegevens.
- Mobiele weergave controleren.

Deze checklist is geen bewijs van uitgevoerde tests. De broncode is geïmporteerd uit het aangeleverde fragment; actieve installatie is niet geverifieerd.

## Versie v0.1.1 – voorgestelde verbetering
De formulierverwerking weigert velden die als array in plaats van tekst worden verstuurd. Ook wordt een niet-tekstuele statusparameter genegeerd. Dit voorkomt dat zulke waarden bij functies terechtkomen die tekst verwachten.

Aanvullende regressiecontroles (nog uitvoeren in een testomgeving):
- Normale reservering blijft werken.
- Verstuur een formulier met bijvoorbeeld `name[]` in plaats van `name`; de aanvraag moet worden geweigerd en mag geen afspraak opslaan.
- Open de reserveringspagina met `kapper_status[]=success`; deze mag geen onterechte succesmelding tonen.

De wijziging is alleen in GitHub doorgevoerd, niet in de actieve WordPress-installatie.
