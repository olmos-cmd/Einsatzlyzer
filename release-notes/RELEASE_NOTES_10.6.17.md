# Einsatzlyzer 10.6.17

## Deutsch

### Einsatzgebiet auf Karten

- Optionales Einsatzgebiet als Polygon auf OpenStreetMap-/Leaflet-Karten ergänzt.
- Neues separates Einstellungsfenster zur Eingabe der Grenzpunkt-Koordinaten.
- Unterstützt eine Koordinate pro Zeile oder `|` als Trennzeichen.
- Auswahl, ob die Umrandung auf der Archiv-/Übersichtskarte, im einzelnen Einsatzbericht oder auf beiden Karten erscheint.
- Offene Polygone werden automatisch geschlossen.
- Ungültige Koordinaten verhindern ein versehentliches Speichern fehlerhafter Gebiete.
- Die Karte berücksichtigt das Polygon beim Zoomen, wenn die Anzeige aktiviert ist.
- Koordinaten und Anzeigeoptionen werden ausdrücklich mit der vollständigen ZIP-Sicherung exportiert und beim Import wiederhergestellt.
- Das bestehende Sicherungsschema 7 bleibt vollständig abwärtskompatibel.

## English

### Response Area on Maps

- Added an optional response-area polygon to OpenStreetMap/Leaflet maps.
- Added a separate settings dialog for entering boundary coordinates.
- Supports one coordinate per line or `|` as a separator.
- Choose whether the boundary is shown on the archive/overview map, individual incident report map, or both.
- Open polygons are automatically closed.
- Invalid coordinates prevent accidental storage of malformed response areas.
- Map fitting includes the polygon whenever it is enabled.
- Coordinates and visibility settings are explicitly exported in complete ZIP backups and restored on import.
- Existing backup schema 7 remains fully backward compatible.
