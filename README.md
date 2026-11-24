# Kapperswebsite

WordPress / Oxygen Builder / PHP / MySQL. Deze repository bevat broncode van losse Code Snippets en documentatie voor B1-K1-W3 (Realiseert onderdelen van software).

## Status
- Reserveringssysteem: aangeleverde broncode staat op `feature/reserveringen` (pull request #1).
- Registratie, login en dashboard: definitieve actieve snippets nog toevoegen.
- WordPress-pagina's en Oxygen-ontwerpen worden apart beheerd; dit is geen volledige website-export.

## Versiebeheer
`main` is de hoofdbranch, `develop` voor integratie, `feature/*` voor afzonderlijke onderdelen. Commits vanaf de import zijn nieuwe wijzigingen, geen reconstructie van eerder ontwikkelwerk. Controleer wijzigingen via pull requests voordat je ze overneemt in WordPress.

## Commitconventie
Gebruik korte Engelstalige commitberichten volgens de structuur `type: omschrijving`.

- `feat:` nieuwe functionaliteit.
- `fix:` oplossen van een fout.
- `docs:` documentatie aanpassen.
- `chore:` onderhoud, configuratie of installatie.
- `refactor:` code herstructureren zonder bedoeld nieuw gedrag.

Een versienummer zoals `[v0.1.1]` kan worden toegevoegd wanneer de wijziging aan een projectversie is gekoppeld. Gebruik alleen een prefix die past bij de daadwerkelijke wijziging. Eerdere commits blijven behouden.

## Privacy
Deze repository is openbaar. Plaats geen klantgegevens, database-export, inloggegevens, API-sleutels of interne configuratie.
