# Einsatzlyzer 10.6.20

## Deutsch

### Neuer Vorschaubild-Fokus pro Einsatz

Version **10.6.20** verbessert die Darstellung zugeschnittener Einsatzbilder in den öffentlichen Einsatzkarten. Wichtige Bildbereiche können jetzt pro Einsatz gezielt im sichtbaren Kartenausschnitt gehalten werden, ohne die feste Kartengröße oder das Originalbild zu verändern.

### Funktionen

- Neuer Bereich **„Vorschaubild-Fokus“** in der Einsatzbearbeitung.
- Voreinstellungen **Mitte**, **Oben**, **Unten**, **Links** und **Rechts**.
- Zusätzlicher Modus **Benutzerdefiniert** mit frei einstellbaren X-/Y-Werten von 0 bis 100 %.
- Direkte Vorschau des späteren Bildausschnitts im WordPress-Backend.
- Bestehende Einsätze bleiben unverändert kompatibel und verwenden standardmäßig **Mitte (50 % / 50 %)**.
- Die feste Kartengröße und `object-fit: cover` bleiben erhalten; nur der Fokuspunkt des Bildausschnitts wird verändert.
- Der gespeicherte Fokus wird auf Einsatzarchiv-Karten, verwandte Einsätze und den Startseiten-Teaser angewendet.
- Originalbild, Einzelansicht und Bildergalerie werden nicht verändert.
- Deutsche und englische Beschriftungen ergänzt.

### Backup / Import / Export

Die neuen Metafelder `_ffl_preview_focus_mode`, `_ffl_preview_focus_x` und `_ffl_preview_focus_y` werden durch die vollständige ZIP-Sicherung automatisch exportiert und wiederhergestellt. Zusätzlich sind sie ausdrücklich im Sicherungsvertrag dokumentiert. Der CSV-Export enthält die Fokuswerte ebenfalls. Das bestehende Sicherungsschema bleibt bei Version 7 und damit abwärtskompatibel.

## English

### New per-incident preview image focus

Version **10.6.20** improves cropped incident images in public incident cards. Important parts of an image can now be kept inside the visible card crop on a per-incident basis without changing the fixed card size or the original image.

### Features

- Added a new **“Preview Image Focus”** section to the incident editor.
- Presets for **Center**, **Top**, **Bottom**, **Left** and **Right**.
- Added a **Custom** mode with freely configurable X/Y values from 0 to 100%.
- Live preview of the resulting crop in the WordPress backend.
- Existing incidents remain fully compatible and default to **Center (50% / 50%)**.
- Fixed card dimensions and `object-fit: cover` are preserved; only the crop focus point changes.
- The saved focus is applied to archive cards, related incidents and the homepage teaser.
- The original image, single incident view and gallery remain unchanged.
- Added German and English labels.

### Backup / Import / Export

The new `_ffl_preview_focus_mode`, `_ffl_preview_focus_x` and `_ffl_preview_focus_y` metadata fields are automatically exported and restored by the complete ZIP backup. They are also explicitly documented in the backup contract. The CSV export includes the focus values as well. The existing backup schema remains at version 7 for backward compatibility.
