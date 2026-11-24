# Änderungsprotokoll – RV Sulz Theme

Versionsschema `MAJOR.MINOR.PATCH`:
- **PATCH** (1.4.**6**): Fehlerbehebungen, kleine Korrekturen
- **MINOR** (1.**5**.0): neue Funktionen, abwärtskompatibel
- **MAJOR** (**2**.0.0): größere Umbauten, Einstellungen müssen evtl. neu gesetzt werden

Die Versionsnummer steht nur im Kopf von `style.css`. Neue Version bauen:
`tools\build.ps1 -Bump patch|minor|major` (siehe readme.txt).

## [1.13.1] – 2026-11-24
### Behoben
- Dunkelmodus: „das erste Training ist gratis.“ auf der Startseite war dunkelblau auf Nachtblau (2,2:1) –
  der Selektor griff nicht; jetzt hellblau (8,1:1).
- Archiv am Handy: Das Suchfeld ragte über den Rand (Seite ließ sich seitlich schieben), „Suchen“ war
  abgeschnitten und 37px hoch – jetzt volle Breite, Knopf 44px.
- Touch-Geräte: Facebook-/Instagram-Symbole im Fußbereich jetzt 44 × 44 (vorher 24 × 24).
- Dachzeile auf dem blauen Verlauf etwas heller (5,1:1 statt 4,3:1 am hellen Ende).
- Dunkelmodus: Hauptknöpfe (Schnuppertraining, Formular, Cookie-Banner) mit hellem Rand, damit sie sich
  vom dunklen Hintergrund abheben.
- Kontaktseite am Handy: „Hier trainieren wir“ steht vor dem Formular (vorher erst nach ca. 1500px);
  Kartenlinks auf Touch-Geräten 44px hoch.

## [1.13.0] – 2026-11-24
### Geändert
- Ladezeit (inc/performance.php):
  - Bild der großen Top-Kachel (größtes sichtbares Element) lädt sofort mit hoher Priorität statt
    „lazy“ (vorher Start erst nach ca. 1,4 s) und in passender Größe.
  - Weißes Logo (48 KB) wird nur noch geladen, wenn es sichtbar ist (Dunkelmodus, Fußbereich).
  - Contact Form 7 lädt Skripte und Styles nur noch auf Seiten mit Formular (vorher 6 Dateien auf jeder Seite).
  - Inter und Barlow Condensed 800 werden vorab geladen – Überschriften springen weniger um.
- 404-Seite: „Abgestiegen!“ statt „Abseits!“ (Abseits gibt es bei Radball und Kunstrad nicht).

## [1.12.0] – 2026-11-24
### Geändert
- Startseite: Die News-Spalten Kunstrad | Radball zeigen keine Beiträge mehr, die schon als Top-Kachel
  oben stehen (vorher erschien ein Beitrag bis zu dreimal).
- Startseite am Handy: die zwei kleinen Top-Kacheln stehen nebeneinander (je ein Sparten-Abzeichen);
  „Probier’s aus“ beginnt dadurch bei ca. 720 statt 1090 px, also auf dem ersten Bildschirm.
- Einheitlicher Abschnitts-Abstand (Rolle --rv-section-space, Stufe XL) für „Probier’s aus“,
  Trainingszeiten, News-Spalten und Archiv/Chronik; das blaue Sparten-Band bleibt großzügiger.
- Sparten-Abzeichen überall gleich (Top-Kacheln, Karten, Beitragskopf, Archiv): gefüllte Pille mit fester
  Sparten-Farbe, 13px. Farben abgedunkelt für ausreichenden Kontrast (Kunstrad 6,5:1, Radball 8,1:1,
  Veranstaltung 5,8:1, Allgemein 6,0:1; vorher Grün 4,3:1 und Orange 3,5:1). Datum in Karten immer
  in eigener Zeile.
- Termine: dekorativer Farbstreifen links an Terminen (Liste und Monatsansicht) entfernt – Karte bzw.
  Fläche tragen die Gruppe allein.
### Behoben
- „Weitere Neuigkeiten“ unter einem Beitrag zeigt den gerade gelesenen Beitrag nicht mehr.

## [1.11.0] – 2026-11-24
### Geändert
- Typografie mit fester Rollen-Skala statt rund 40 Einzelwerten: Etikett 13px, Meta 15px, Text 18px
  (vorher 17px), Einleitung 18–22px, Titel 20–24px, großer Titel 26–36px (CSS-Variablen --rv-type-*,
  neue Schriftgrößen „Etikett“ und „Titel“ im Editor). Kalender-Monatsraster bleibt kompakt.
- Lesebreite 660px statt 760px: Beiträge und Seiten haben jetzt ca. 75 statt 92 Zeichen pro Zeile.
- Alle Knöpfe einheitlich im Knopf-Stil des Themes (Barlow Condensed, Großbuchstaben).
- Überschriften brechen ausgewogen um (text-wrap: balance), Fließtext ohne einzelne Wörter in der
  letzten Zeile (text-wrap: pretty).

## [1.10.2] – 2026-11-24
### Behoben
- Dunkelmodus: Als Link gebaute Knöpfe („Schnuppertraining anfragen“, „Mehr laden“ im Archiv, Kacheln
  „Archiv“/„Chronik“) bekamen hellblaue Schrift auf Blau (Kontrast 3,8:1) – jetzt weiß (8,1:1).
- Touch-Geräte: Tippflächen mindestens 44 × 44 px – Menü-Knopf (vorher 24 px), Social-Symbole,
  Dunkelmodus-Umschalter (38 px), „Alle →“ (42 × 17), Fußzeilen-Links und „Cookie-Einstellungen“ (16–22 px hoch),
  Archiv-Filter und Seitenzahlen. Nur die Klickfläche wächst, das Aussehen am Desktop bleibt gleich.

## [1.10.1] – 2026-11-24
### Entfernt
- Werkzeug „Alte Website importieren“ wieder entfernt – der einmalige Import ist erledigt
  (118 Dateien in der Mediathek, Links umgestellt). Mediathek und Beiträge bleiben unverändert.

## [1.10.0] – 2026-11-24
### Neu
- Werkzeuge → „Alte Website importieren“: holt PDFs, Excel-/Word-Dateien, Bilder und Videos, auf die
  Beiträge und Seiten noch bei www.rv-sulz.at verlinken, in die Mediathek (am Beitrag angehängt) und
  stellt die Links auf relative Pfade um. HTML-Seiten der alten Website bleiben verlinkt. Bereits
  importierte Dateien werden erkannt; jede Inhaltsänderung ist als Revision gespeichert (inc/import-alt.php).

## [1.9.0] – 2026-11-24
### Neu
- Einwilligungs-Banner für die Besucherstatistik: Google Analytics (Plugin „Site Kit“) lädt erst nach
  „Erlauben“; vorher keine Verbindung zu Google und keine Cookies. „Ablehnen“ und „Erlauben“ sind
  gleichwertig, Link zur Datenschutzerklärung. „Cookie-Einstellungen“ in der Fußzeile öffnet das Banner
  wieder; beim Widerruf werden die Statistik-Cookies gelöscht. Wahl nur im Browser, 12 Monate gültig.
  Ohne Statistik-Skript erscheinen weder Banner noch Link (inc/consent.php, assets/js/consent.js).

## [1.8.0] – 2026-11-24
### Neu
- Startseite auf neue Mitglieder ausgerichtet. Neue Reihenfolge: Top-Beiträge → „Probier’s aus“
  (Knopf „Schnuppertraining anfragen“ und drei Schritte) → Trainingszeiten → News Kunstrad | Radball
  → Sparten-Band → Kacheln „Beiträge im Archiv“ und „Chronik“.
- Customizer-Bereich „Mitmachen & Training“: Überschrift, Text, Knopf, drei Schritte; je Sparte
  Wann, Wo, Alter, Kosten, Ansprechperson (Funktion) und Foto; Gründungsjahr und Chronik-Adresse.
  Trainingszeiten erscheinen erst, wenn „Wann“ ausgefüllt ist.
- Drei neue Blöcke für den Website-Editor: „RV Sulz: Probier’s aus“, „RV Sulz: Trainingszeiten“,
  „RV Sulz: Archiv & Chronik“.
- Formulare aus „Contact Form 7“ im Theme-Design: Felder, Auswahl, Zustimmungs-Kästchen, Absenden-Knopf,
  Fehler- und Erfolgsmeldungen, Dunkelmodus (assets/css/forms.css).
- Muster „Kontakt & Schnuppertraining“: Anfrage-Formular, Trainingsort mit Kartenlink und
  Ansprechpersonen je Funktion. Formular-Vorlage zum Kopieren: docs/contact-form-7-schnuppertraining.txt.
### Geändert
- Das Neuigkeiten-Raster (mit Seitenblättern) und das Band „Komm ins Training!“ sind nicht mehr
  Teil der Startseite; der Aufruf steht jetzt oben. Die Muster bleiben im Editor verfügbar.
- Seiten (Vorlage „Seite“): breite Muster dürfen jetzt die volle Breite (1240px) nutzen,
  normaler Text bleibt schmal (760px).

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
