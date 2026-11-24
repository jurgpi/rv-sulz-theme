# Änderungsprotokoll – RV Sulz Theme

Versionsschema `MAJOR.MINOR.PATCH`:
- **PATCH** (1.4.**6**): Fehlerbehebungen, kleine Korrekturen
- **MINOR** (1.**5**.0): neue Funktionen, abwärtskompatibel
- **MAJOR** (**2**.0.0): größere Umbauten, Einstellungen müssen evtl. neu gesetzt werden

Die Versionsnummer steht nur im Kopf von `style.css`. Neue Version bauen:
`tools\build.ps1 -Bump patch|minor|major` (siehe readme.txt).

## [1.7.0] – 2026-11-20
### Neu
- Muster „Vorstand“: Obmann als breite Karte, Karten für Funktionäre und sportliche Leitung
  (Foto oder Personen-Symbol, Funktion als Abzeichen, Aufgabe, Kontakt-Link), Kassaprüfer als
  Namens-Chips und Mitmach-Aufruf. Inkl. Dunkelmodus und Handy-Ansicht.
- Muster „Ehrenmitglieder“ (Karten mit Foto oder Stern) und Menüpunkt „Ehrenmitglieder“ unter Verein.
- Chronik-Seite: Design für den eigenen HTML-Block (assets/css/chronik.css), nur dort geladen.
### Behoben
- Archiv: Zurück-Taste bei aktivem Suchfeld lud die ganze Seite neu und setzte den alten Suchbegriff wieder ein.

## [1.6.1] – 2026-11-19
### Behoben
- Update-Prüfung: „Erneut überprüfen“ unter Dashboard → Aktualisierungen holt den neuesten
  GitHub-Release jetzt sofort. Bisher konnte ein neues Release bis zu einer Stunde lang
  unsichtbar bleiben, weil die gespeicherte Antwort von GitHub nicht verworfen wurde.

## [1.6.0] – 2026-11-19
### Neu
- Durchsuchbares Archiv: Block „RV Sulz: Archiv“ (auch als Shortcode `[rv_archiv]`) mit
  Jahres-Leiste samt Beitragsanzahl, Filter nach Sparte (Kategorie), Suchfeld und Beitragskarten
  mit Bild. Filter und „Mehr laden“ ohne Neuladen der Seite; ohne JavaScript über normale Links.
- Vorlage `page-archiv` für die Seite /archiv/: zeigt das neue Archiv automatisch; der alte
  Block „Archiv“ (Jahresliste) wird dort ausgeblendet.

 

## [1.5.1] – 2026-11-19
### Geändert
- Beschreibungen ohne Verweise auf andere Themes: README/GitHub-Startseite, readme.txt und
  Beschreibung des Customizer-Bereichs „Startseite“. Funktion unverändert.
- GitHub-Startseite um „Updates direkt aus GitHub“ ergänzt.

## [1.5.0] – 2026-11-19
### Neu
- Updates direkt aus GitHub: Das Theme meldet neue Versionen unter Dashboard → Aktualisierungen
  (WordPress-Schnittstelle „Update URI“, kein Plugin). Quelle ist das ZIP im neuesten
  GitHub-Release; für das private Repository Lese-Token `RV_SULZ_GITHUB_TOKEN` in `wp-config.php`.
- Build-Skript: `-Push` legt zusätzlich das GitHub-Release mit ZIP an; `-GitHubRelease` holt
  ein Release für die aktuelle Version nach.
### Geändert
- Theme-Autor im Kopf von `style.css` ist jetzt der Verein statt einer Person.

## [1.4.6] – 2026-11-19
### Geändert
- Versionierung eingeführt: Version wird nur noch in `style.css` gepflegt
  (`functions.php` liest sie aus), Änderungsprotokoll, Build-Skript `tools\build.ps1`
  mit `-Bump`, `-Release` und `-Push`; ZIPs liegen jetzt in `dist\rv-sulz-<version>.zip`.
- readme.txt: Abschnitt „Versionierung & GitHub“.

## [1.4.5] – 2026-11-19
Stand bei Einführung der Versionierung (Git-Tag `v1.4.5`). Frühere Änderungen sind nicht
einzeln protokolliert.
### Enthalten
- Block-Theme mit Startseite: Top-Beiträge, News-Spalten Kunstrad | Radball, Sparten-Band,
  Neuigkeiten-Raster, Mitmach-Aufruf – einstellbar unter Customizer → Startseite.
- Menüs und Fußbereich verwaltbar wie bei HybridMag (Design → Menüs / Widgets).
- Seiten /kunstrad/ und /radball/ mit den neuesten Beiträgen der Kategorie.
- Dunkelmodus mit Umschalter, weißes Logo im Dunkelmodus.
- Design für Termine aus „Simple Calendar“ (Monats- und Listenansicht).
- Lokal eingebundene Schriften (Inter, Barlow Condensed), keine Google-Verbindung.
