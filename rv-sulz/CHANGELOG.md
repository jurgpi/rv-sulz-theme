# Änderungsprotokoll – RV Sulz Theme

Versionsschema `MAJOR.MINOR.PATCH`:
- **PATCH** (1.4.**6**): Fehlerbehebungen, kleine Korrekturen
- **MINOR** (1.**5**.0): neue Funktionen, abwärtskompatibel
- **MAJOR** (**2**.0.0): größere Umbauten, Einstellungen müssen evtl. neu gesetzt werden

Die Versionsnummer steht nur im Kopf von `style.css`. Neue Version bauen:
`tools\build.ps1 -Bump patch|minor|major` (siehe readme.txt).

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
