# Einsatzlyzer 10.6.19

## Deutsch

### Neuer serverseitiger Startseiten-Einsatzteaser

Version **10.6.19** ergänzt den neuen Shortcode:

```text
[ffl_startseite_einsaetze]
```

Der Shortcode wird vollständig serverseitig erzeugt und funktioniert auch im Elementor-Shortcode-Widget. Für die Teaser-Ausgabe ist kein JavaScript erforderlich.

### Funktionen

- Automatische Anzeige der Anzahl aller veröffentlichten Einsätze des aktuellen Jahres.
- Automatische Ausgabe der neuesten veröffentlichten Einsätze.
- Ein neuer veröffentlichter Einsatz erscheint automatisch an Position 1; der bisher älteste sichtbare Eintrag fällt entsprechend heraus.
- Anzahl der sichtbaren Einsätze frei zwischen **1 und 5** einstellbar; Standard ist **3**.
- Pro Einsatz werden Datum, Titel und der Link **„Einsatz ansehen →“** angezeigt.
- Einsatzbilder werden als vollflächige Kartenhintergründe mit dunklem Verlaufs-Overlay ausgegeben.
- Lange Einsatztitel werden responsiv begrenzt und laufen nicht aus der Karte heraus.
- Responsive Darstellung für Desktop, Tablet und Mobilgeräte.
- Der Link **„Alle Einsätze →“** verwendet die bestehende Funktion `ffl_get_archive_url()`.
- Einsatzlinks verwenden die bestehende Funktion `ffl_get_incident_permalink()`.

### Bild-Fallback

Die Bildauswahl erfolgt in dieser Reihenfolge:

1. Beitragsbild
2. erstes Bild aus dem Einsatzbericht
3. erstes Galeriebild aus `_ffl_gallery_ids`
4. neutrale dunkle Hintergrundfläche

### Einstellungen

Unter **Einsatzlyzer → Einstellungen** stehen neu zur Verfügung:

- **Startseiten-Einsatzteaser aktivieren** – Ein/Aus, standardmäßig Ein
- **Anzahl Einsätze auf der Startseite** – 1 bis 5, Standard 3

Die Optionen werden intern als `ffl_home_incidents_enabled` und `ffl_home_incidents_count` gespeichert.

### Backup / Import / Export

Beide neuen Einstellungen werden ausdrücklich in der vollständigen ZIP-Sicherung dokumentiert, exportiert, beim Import wiederhergestellt und anschließend verifiziert. Das bestehende Sicherungsschema bleibt bei Version 7 und damit kompatibel.

## English

### New server-rendered homepage incident teaser

Version **10.6.19** adds the new shortcode:

```text
[ffl_startseite_einsaetze]
```

The shortcode is rendered entirely on the server and works in the Elementor Shortcode widget. No JavaScript is required for the teaser output.

### Features

- Automatically displays the number of all published incidents in the current year.
- Automatically displays the latest published incidents.
- A newly published incident automatically moves to position 1, while the oldest visible card drops out.
- Number of visible incidents can be configured from **1 to 5**, default **3**.
- Each card displays date, title and **“View Incident →”**.
- Incident images are used as full-card backgrounds with a dark gradient overlay.
- Long incident titles are responsively clamped so they cannot overflow the card.
- Responsive layout for desktop, tablet and mobile devices.
- The **“All Incidents →”** link uses the existing `ffl_get_archive_url()` helper.
- Incident links use the existing `ffl_get_incident_permalink()` helper.

### Image fallback

Images are selected in this order:

1. featured image
2. first image embedded in the incident report
3. first gallery image from `_ffl_gallery_ids`
4. neutral dark background

### Settings

New options under **Einsatzlyzer → Settings**:

- **Enable Homepage Incident Teaser** – On/Off, enabled by default
- **Number of Incidents on the Homepage** – 1 to 5, default 3

The options are stored as `ffl_home_incidents_enabled` and `ffl_home_incidents_count`.

### Backup / Import / Export

Both new settings are explicitly documented in the complete ZIP backup contract, exported, restored during import and verified afterwards. The existing backup schema remains at version 7 for compatibility.
