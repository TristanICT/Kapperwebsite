# Kapperswebsite

Online reserveringssysteem voor een kapsalon, ontwikkeld voor WordPress met Oxygen Builder, Code Snippets, PHP en MySQL.

## Projectstructuur

| Bestand | Functie | Status |
|---|---|---|
| `snippets/reserveren.php` | Reserveringsformulier, databaseopslag en bevestigingsmail | Aangeleverde broncode met latere validatieverbetering |
| `snippets/registratie.php` | Klantaccount aanmaken | Concept, actieve snippet vergelijken |
| `snippets/login.php` | Inloggen en doorverwijzen | Concept, actieve snippet vergelijken |
| `snippets/dashboard.php` | Afsprakenoverzicht in WordPress | Concept, actieve snippet vergelijken |
| `docs/` | Installatie, testcontroles en versiebeheer | Documentatie |
| `CHANGELOG.md` | Versiewijzigingen | Reserveringsmodule |

## Installatie

1. Gebruik een WordPress-testomgeving met Code Snippets.
2. Maak per PHP-bestand een aparte snippet en laat de openingsregel `<?php` weg. Vervang niet zomaar bestaande actieve snippets; vergelijk ze eerst.
3. Activeer de reserveringssnippet en plaats `[kapper_reserveren]` op de reserveringspagina.
4. Gebruik voor de conceptformulieren `[custom_register]` en `[custom_login]`. Controleer de pagina-slugs.
5. Het dashboard verschijnt voor bevoegde beheerders in het WordPress-menu.
6. Test de werking en rechten met fictieve accounts en afspraken.

De repository is geen volledige WordPress- of Oxygen-export. De actieve registratie-, login-, dashboard- en eventuele accountpagina-snippets moeten nog worden vergeleken of geëxporteerd. Wijzigen en annuleren van afspraken zijn nog niet bevestigd als gerealiseerd.

## Versiebeheer

- `main`: geïntegreerde projectbestanden; geen bevestigde live release.
- `develop`: integratiebranch.
- `feature/*`: afzonderlijke onderdelen en wijzigingen.
- Gebruik een pull request om wijzigingen te vergelijken voordat ze worden samengevoegd.

Commitberichten volgen de structuur `type: omschrijving`:

| Prefix | Betekenis |
|---|---|
| feat | Nieuwe functionaliteit |
| fix | Fout oplossen |
| docs | Documentatie |
| chore | Onderhoud en configuratie |
| refactor | Code herstructureren zonder bedoeld gewijzigd gedrag |

Versienummers zoals `[v0.1.1]` verwijzen naar vastgelegde wijzigingen in het reserveringsonderdeel. Zie de [changelog](CHANGELOG.md) en de [handleiding versiebeheer](docs/versiebeheer.md).

## Privacy en security

Deze repository is openbaar. Publiceer geen echte klantgegevens, database-exports, inloggegevens, API-sleutels of interne configuratie. Test met fictieve gegevens.

## Projectstatus

De repository bevat de beschikbare broncode, technische documentatie en versiegeschiedenis. De werking van de conceptimplementaties en recente wijzigingen moet nog in een WordPress-testomgeving worden gecontroleerd. Zie [K1-W3](docs/k1-w3.md) voor de verdere verantwoording.
