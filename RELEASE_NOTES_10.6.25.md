# Einsatzlyzer 10.6.25

## Deutsch

### Vorschaubild-Fokus im Frontend ohne `object-position`
Die öffentliche Einsatzkarte verwendet ab 10.6.25 eine eigene sichtbare Hintergrundebene. Das Bild und die gespeicherten Fokuswerte werden dort direkt als `background-image` und `background-position` ausgegeben. Damit kann ein Lazyload- oder Bildoptimierungs-Plugin den sichtbaren Fokus nicht mehr dadurch verlieren, dass es das eigentliche `<img>` verändert oder ersetzt.

- X/Y-Fokuswerte wirken direkt auf die sichtbare Kartenebene.
- Das Kartenformat bleibt unverändert.
- Das ursprüngliche Bild bleibt mit ALT-Text im HTML erhalten.
- Startseiten-Teaser erhält die Fokusposition ebenfalls direkt.
- Keine Datenmigration erforderlich.
- Backup-Schema bleibt Version 7.

## English

### Frontend preview focus no longer depends on `object-position`
Starting with 10.6.25, public incident cards use a dedicated visible background layer. The image and stored focal-point values are emitted directly as `background-image` and `background-position`, so lazy-loading or image-optimization plugins cannot lose the visible focus by replacing or rewriting the underlying `<img>`.

- X/Y focal values are applied directly to the visible card layer.
- Card dimensions remain unchanged.
- The original image stays in the HTML with its ALT text.
- Homepage teasers also receive the focal position directly.
- No data migration is required.
- Backup schema remains version 7.
