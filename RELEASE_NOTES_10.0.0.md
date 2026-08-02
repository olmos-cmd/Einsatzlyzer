# Einsatzlyzer 10.0.0

[English](#english) · [Deutsch](#deutsch)

## English

### New language system

The plugin language system has been rebuilt. German remains unchanged. When **English** is selected, only fixed Einsatzlyzer interface texts are displayed in English in the WordPress admin area and on the frontend.

### Important fixes

- Fixed infinite recursion in `ffl_archive_date()`
- Removed unsafe word-by-word frontend translation
- User-entered incident titles, summaries, locations and report texts are no longer modified
- Terms such as “Einsatzübung” remain exactly as entered
- Admin translation is limited to complete, known interface labels
- WordPress, the active theme and other plugins keep their own language

### Updating

Install `einsatzlyzer-10.0.0.zip` through **Plugins → Add New → Upload Plugin**. WordPress can replace the existing plugin installation while keeping the incident reports and settings.

A complete backup of the WordPress files and database is recommended before updating.

---

## Deutsch

### Neues Sprachsystem

Die Sprachumschaltung wurde technisch neu aufgebaut. Deutsch bleibt unverändert. Bei Auswahl von **English** werden ausschließlich feste Texte von Einsatzlyzer im WordPress-Backend und Frontend englisch ausgegeben.

### Wichtige Korrekturen

- Endlosschleife in `ffl_archive_date()` behoben
- unsichere wortweise Übersetzung aus dem Frontend entfernt
- keine Veränderungen mehr an Einsatzüberschriften, Kurzfassungen, Einsatzorten oder Berichtstexten
- Begriffe wie „Einsatzübung“ bleiben als eingegebener Inhalt vollständig erhalten
- Backend-Übersetzung erfolgt nur noch bei vollständigen, bekannten Oberflächentexten
- WordPress, das aktive Theme und andere Plugins behalten ihre eigene Sprache

### Aktualisierung

Die Datei `einsatzlyzer-10.0.0.zip` kann in WordPress über **Plugins → Plugin hinzufügen → Plugin hochladen** über die vorhandene Installation installiert werden. Einsatzberichte und Einstellungen bleiben dabei erhalten.

Vor dem Update wird eine vollständige Sicherung der WordPress-Dateien und Datenbank empfohlen.
