# Einsatzlyzer 10.6.9

## Deutsch

- Neues Einstellungsfenster „Entfernung zum Einsatzort“.
- Auswahl zwischen Nicht anzeigen, Luftlinie, Fahrstrecke, Fahrzeit, Fahrstrecke und Fahrzeit sowie Luftlinie/Fahrstrecke/Fahrzeit.
- Straßenroute und Fahrzeit werden über OSRM mit OpenStreetMap-Daten berechnet.
- Ergebnisse werden pro Einsatz gespeichert und nicht bei jedem Seitenaufruf neu abgefragt.
- Stapelverarbeitung für alle veröffentlichten Einsätze mit Fortschrittsbalken, Pause, Fortsetzen, Abbrechen und Fehlerliste.
- Nur fehlende Werte, alle Werte oder nur Fehler können verarbeitet werden.
- Geänderte Feuerwehrhaus- oder Einsatzkoordinaten werden erkannt; veraltete Werte werden nicht angezeigt.

## English

- Added a new “Distance to Incident Location” settings dialog.
- Display options include hidden, straight-line distance, driving distance, driving time, combined driving distance/time, or all values.
- Road distance and estimated driving time are calculated through OSRM using OpenStreetMap data.
- Results are stored per incident and are not requested on every page view.
- Batch processing for all published incidents includes progress, pause, resume, stop and error reporting.
- Process missing values, recalculate all values or retry errors.
- Changed station or incident coordinates invalidate outdated stored routing values.
