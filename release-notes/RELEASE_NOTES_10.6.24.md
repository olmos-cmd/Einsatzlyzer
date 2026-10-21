# Einsatzlyzer 10.6.24

## Deutsch

### Vorschaubild-Fokus jetzt auch zuverlässig im Frontend
- Gespeicherte Fokuswerte werden auf öffentlichen Einsatzkarten über CSS-Variablen am Karten-Wrapper ausgegeben.
- Die Bildposition wird zentral in `einsatzlyzer.css` mit `object-position` und den gespeicherten X-/Y-Werten gesetzt. Dadurch bleibt der Fokus auch erhalten, wenn Lazyload- oder Bildoptimierungs-Plugins das eigentliche `<img>` austauschen oder verändern.
- Der Startseiten-Shortcode verwendet dieselben CSS-Variablen für `background-position`, sodass Backend-Vorschau, Einsatzarchiv und Startseiten-Teaser dieselbe Fokusposition verwenden.
- Beim Speichern eines Einsatzes werden WordPress-Post-Caches bereinigt. Wenn W3 Total Cache die URL-Purge-Funktion bereitstellt, werden Einsatzbericht, Einsatzübersicht und Startseite gezielt invalidiert. Ein globaler Cache-Flush wird bewusst nicht erzwungen.
- Bestehende Fokuswerte sowie Backup-/Restore-Daten bleiben unverändert kompatibel; Sicherungsschema bleibt Version 7.

## English

### Preview-image focus now applied reliably on the frontend
- Stored focal-point values are exposed as CSS custom properties on public incident-card wrappers.
- Image positioning is applied centrally in `einsatzlyzer.css` using the stored X/Y values, keeping the focal point intact even when lazy-loading or image-optimization plugins replace or modify the actual `<img>`.
- The homepage shortcode uses the same CSS variables for `background-position`, so the editor preview, incident archive and homepage teaser share the same focal point.
- Saving an incident clears WordPress post caches. When W3 Total Cache exposes its URL-purge API, the incident URL, archive URL and homepage are invalidated selectively. A global cache flush is intentionally avoided.
- Existing focal-point metadata and backup/restore remain fully compatible; backup schema stays at version 7.
