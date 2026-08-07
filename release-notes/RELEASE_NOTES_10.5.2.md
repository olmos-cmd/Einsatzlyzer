# Einsatzlyzer 10.5.2

## English

Version 10.5.2 verifies and explicitly documents the complete import and export of all weather-related data.

- Complete backup schema Version 3
- Default fire-station location name, latitude and longitude always included
- Incident coordinates and all `_ffl_` weather metadata included
- Stored coordinate source and coordinate label preserved
- Open-Meteo request URL, timezone, retrieval time and model note preserved
- Weather errors and error timestamps preserved
- Post-import verification of default-location settings
- Backward compatible with older Einsatzlyzer backups
- New automated regression test

## Deutsch

Version 10.5.2 sichert und dokumentiert den vollständigen Import und Export aller wetterbezogenen Daten ausdrücklich.

- vollständiges Sicherungsschema Version 3
- Name, Breitengrad und Längengrad des Feuerwehrhaus-/Standardstandorts immer enthalten
- Einsatzkoordinaten und sämtliche `_ffl_`-Wettermetadaten enthalten
- verwendete Koordinatenquelle und Bezeichnung bleiben erhalten
- Open-Meteo-Abfrage-URL, Zeitzone, Abrufzeitpunkt und Modellhinweis bleiben erhalten
- Wetterfehler und Fehlerzeitpunkte bleiben erhalten
- Kontrolle der Standardstandort-Einstellungen nach dem Import
- kompatibel mit älteren Einsatzlyzer-Sicherungen
- neuer automatischer Regressionstest
