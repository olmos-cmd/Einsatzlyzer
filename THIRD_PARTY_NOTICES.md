# Third-party notices / Hinweise zu Drittanbieterbestandteilen

## English

Einsatzlyzer contains or uses third-party components and external data sources. These components and data sources retain their respective licenses and copyright notices.

### Leaflet

- Project: Leaflet
- Version: 1.9.4
- Purpose: interactive maps
- License: BSD-2-Clause
- Project website: https://leafletjs.com/

### OpenStreetMap

Map data is provided by OpenStreetMap contributors. The required attribution is displayed directly on every OpenStreetMap-based map.

- Data source: OpenStreetMap
- Attribution: © OpenStreetMap contributors
- Data license: Open Database License (ODbL)
- Copyright information: https://www.openstreetmap.org/copyright

### OpenTopoMap

Einsatzlyzer can optionally display a topographic map layer provided by OpenTopoMap. When this layer is active, the required OpenTopoMap attribution is displayed together with the OpenStreetMap data attribution on the map.

- Map layer: OpenTopoMap
- Map data: © OpenStreetMap contributors
- Map rendering: © OpenTopoMap contributors
- Project website: https://opentopomap.org/
- License information: https://opentopomap.org/about

### OSRM

Einsatzlyzer can use an OSRM routing service to calculate road distance and estimated driving time using OpenStreetMap road data.

- Project: Open Source Routing Machine (OSRM)
- Project website: https://project-osrm.org/
- Source code: https://github.com/Project-OSRM/osrm-backend

This file does not replace the complete license texts or terms of the respective projects and data providers.

---

## Deutsch

Einsatzlyzer enthält oder verwendet Bestandteile von Drittanbietern sowie externe Datenquellen. Diese Bestandteile und Datenquellen behalten ihre jeweiligen Lizenzen und Urheberrechtshinweise.

### Leaflet

- Projekt: Leaflet
- Version: 1.9.4
- Zweck: interaktive Karten
- Lizenz: BSD-2-Clause
- Projektseite: https://leafletjs.com/

### OpenStreetMap

Die Kartendaten stammen von OpenStreetMap-Mitwirkenden. Die vorgeschriebene Quellenangabe wird direkt auf jeder OpenStreetMap-basierten Karte eingeblendet.

- Datenquelle: OpenStreetMap
- Quellenangabe: © OpenStreetMap-Mitwirkende
- Lizenz der Daten: Open Database License (ODbL)
- Hinweise: https://www.openstreetmap.org/copyright

### OpenTopoMap

Einsatzlyzer kann optional eine topografische Kartenebene von OpenTopoMap anzeigen. Wenn diese Kartenebene aktiv ist, wird die erforderliche OpenTopoMap-Quellenangabe zusammen mit der OpenStreetMap-Attribution direkt auf der Karte angezeigt.

- Kartenebene: OpenTopoMap
- Kartendaten: © OpenStreetMap-Mitwirkende
- Kartendarstellung: © OpenTopoMap-Mitwirkende
- Projektseite: https://opentopomap.org/
- Lizenzinformationen: https://opentopomap.org/about

### OSRM

Einsatzlyzer kann einen OSRM-Routingdienst verwenden, um Fahrstrecke und geschätzte Fahrzeit auf Grundlage von OpenStreetMap-Straßendaten zu berechnen.

- Projekt: Open Source Routing Machine (OSRM)
- Projektseite: https://project-osrm.org/
- Quellcode: https://github.com/Project-OSRM/osrm-backend

Diese Datei ersetzt nicht die vollständigen Lizenztexte oder Nutzungsbedingungen der jeweiligen Projekte und Datenanbieter.

## Einsatzlyzer pure-PHP PDF text extractor

The bundled lightweight extractor in `includes/simple-pdf-text.php` is original Einsatzlyzer code and is distributed under the plugin's GPL-2.0-or-later license. It uses only PHP's standard `iconv` and `zlib` extensions. No external PDF parser package is included.
