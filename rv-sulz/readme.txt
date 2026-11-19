=== RV Sulz ===
Block-Theme für den RV Enzian Sulz (Kunstrad & Radball). Benötigt WordPress 6.6+.

== Installation ==
1. Design → Themes → Theme hinzufügen → Theme hochladen → dist\rv-sulz-<version>.zip → Aktivieren
   (bei einem Update „Aktuelle Version ersetzen“ wählen).
2. Das bestehende Website-Logo wird automatisch übernommen (sonst wird das mitgelieferte Vereinslogo gezeigt).
3. Menüs wie bei HybridMag unter Design → Menüs pflegen. Positionen:
   * Hauptmenü (Kopfbereich) – heißt intern wie bei HybridMag "primary"; die bisherige Zuordnung
     wird beim Theme-Wechsel normalerweise übernommen (sonst dort einmal anhaken).
   * Fußbereich – Spalte „Sport“ / Spalte „Verein“, Social-Media-Links (Facebook/Instagram werden erkannt).
   Ohne zugewiesenes Menü zeigt das Theme Vorgabe-Einträge. Im Website-Editor sieht man immer
   diese Vorgabe-Einträge – das zugewiesene Menü erscheint erst auf der Website.
   Untermenüs: Am Desktop wird der Hauptpunkt (z. B. „Radball“) im Aufklappmenü nochmals als
   erster Unterpunkt gezeigt, im Handy-Menü nicht (dort ist der Hauptpunkt selbst anklickbar).

== Aufbau ==
* Startseite (front-page): Top-Beiträge, News Kunstrad | Radball, Sparten-Band, Neuigkeiten-Raster, Mitmach-Aufruf.
* Seiten /kunstrad/ und /radball/: Seitentext + automatisch die neuesten Beiträge der Kategorie (page-kunstrad / page-radball).
* Startseite einstellen (wie bei HybridMag): Design → Customizer → Startseite.
  Für jeden Bereich wählbar: Kategorie, Anzahl Beiträge, Überschrift, anzeigen ja/nein
  (Top-Beiträge, zwei News-Spalten, Sparten-Band, Neuigkeiten-Raster, „Komm ins Training!“).
  Vorgabe: linke Spalte Kunstrad, rechte Spalte Radball (über den Kategorie-Slug gefunden).
* Seiten /kunstrad/ und /radball/ zeigen fest Kategorie-ID 3 bzw. 4 – bei anderen IDs in
  templates/page-*.html anpassen (oder im Editor den Abfrage-Loop umstellen).
* Fußbereich verwalten (wie bei HybridMag):
  - Design → Widgets: „Fußbereich 1–4“ = die vier Spalten. Leer = Vorgabe des Themes;
    die Spalten „Sport“ und „Verein“ werden ohne Widget und ohne zugewiesenes Menü ausgeblendet.
    Für eine Menüspalte das Widget „Navigationsmenü“ nehmen (Titel = Überschrift, Menü auswählen).
  - Design → Menüs → Position „Fußzeile unten“: Links neben dem Copyright (Kontakt, Impressum, …).
  - Design → Customizer → Fußbereich: Copyright-Text ({jahr} = aktuelles Jahr).
  - Auf dem Handy werden die Spalten 2 und 3 ausgeblendet (dort gibt es das Hauptmenü oben).
* Dunkelmodus: folgt automatisch der Geräteeinstellung; Besucher können oben per Mond/Sonne-Knopf umschalten
  (Wahl wird nur im Browser gespeichert, kein Cookie). Im Dunkelmodus erscheint das weiße Logo.
* Termine (Plugin „Simple Calendar“): Monats- und Listenansicht werden automatisch im Theme-Design
  gezeigt (assets/css/simple-calendar.css, inkl. Dunkelmodus und Handy). Nichts einzustellen.
* Schriften (Inter, Barlow Condensed) sind lokal eingebunden – keine Verbindung zu Google (DSGVO).

== Versionierung & GitHub ==
* Die Versionsnummer steht nur im Kopf von style.css (Zeile „Version:“). Änderungen in CHANGELOG.md.
* Privates Repository: https://github.com/jurgpi/rv-sulz-theme
  Die Git-Datenbank liegt außerhalb von OneDrive (C:\Users\juergen\git\rv-sulz-theme.git).
* Neue Version (im Ordner CustomTemplate):
  1. powershell -ExecutionPolicy Bypass -File tools\build.ps1 -Bump patch   (bzw. minor / major)
  2. CHANGELOG.md ausfüllen, testen
  3. powershell -ExecutionPolicy Bypass -File tools\build.ps1 -Force -Release -Push
     → ZIP in dist\, Commit „Version x.y.z“, Tag vx.y.z, Upload zu GitHub.

== Lizenz ==
GPL v2 oder später. Schriften: SIL Open Font License.
