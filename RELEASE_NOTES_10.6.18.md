# Einsatzlyzer 10.6.18

## Deutsch

### Sichtbarkeit des Einsatzgebiet-Polygons korrigiert

- Darstellungsfehler behoben, durch den das Einsatzgebiet zwar beim Karten-Zoom berücksichtigt, die Umrandung selbst aber nicht sichtbar gezeichnet wurde.
- Ursache war eine allgemeine Einsatzlyzer-SVG-Regel, die auch den von Leaflet erzeugten großen SVG-Renderer der Kartenebene auf Icon-Größe verkleinerte.
- Leaflet-Overlay-SVGs werden jetzt gezielt von der Icon-Darstellung ausgenommen.
- Das Einsatzgebiet erhält eine eigene CSS-Klasse und eine gut sichtbare rote Umrandung mit dezenter transparenter Fläche.
- Archiv-/Übersichtskarte und Karte im einzelnen Einsatzbericht verwenden dieselbe korrigierte Darstellung.
- Einstellungen, Koordinaten und vollständige Backup-/Restore-Unterstützung aus 10.6.17 bleiben unverändert erhalten.

## English

### Fixed response-area polygon visibility

- Fixed a rendering issue where the response area affected map fitting but the boundary itself was not visibly drawn.
- The cause was a generic Einsatzlyzer SVG icon rule that also reduced Leaflet's full-size map overlay SVG to icon dimensions.
- Leaflet overlay SVGs are now explicitly excluded from the icon sizing rules.
- The response area now has its own CSS class with a clearly visible red outline and a subtle transparent fill.
- The archive/overview map and individual incident-report map use the same corrected rendering.
- Settings, coordinates and complete backup/restore support introduced in 10.6.17 remain unchanged.
