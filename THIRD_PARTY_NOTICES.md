# Third-party notices / Hinweise zu Drittanbieterbestandteilen

## English

Einsatzlyzer contains, uses or connects to third-party components and data services. These components and services retain their respective licenses, copyright notices and terms of use.

### Leaflet

- Project: Leaflet
- Version: 1.9.4
- Purpose: interactive maps
- License: BSD-2-Clause
- Project website: https://leafletjs.com/

### OpenStreetMap

Map data is provided by OpenStreetMap contributors. The required attribution is displayed directly on maps using OpenStreetMap data.

- Data source: OpenStreetMap contributors
- Data license: Open Database License (ODbL) 1.0
- Copyright and attribution information: https://www.openstreetmap.org/copyright

### OpenTopoMap

OpenTopoMap is available as an optional topographic map layer. Its attribution is displayed when that layer is used.

- Map data: © OpenStreetMap contributors, ODbL
- Map style / cartography: © OpenTopoMap contributors, CC BY-SA 3.0
- Project website: https://opentopomap.org/

### OSRM

Einsatzlyzer can use the Open Source Routing Machine (OSRM) to calculate driving distance and estimated travel time from stored coordinates. The public OSRM service is an external service and is not bundled with Einsatzlyzer.

- Project: OSRM Backend
- License: BSD-2-Clause
- Project repository: https://github.com/Project-OSRM/osrm-backend

### Map attribution

Einsatzlyzer displays the applicable attribution directly on the map. Attribution must not be hidden or removed by themes, custom CSS or other plugins.

This file does not replace the complete license texts or terms of use of the respective projects and services.

---

## Deutsch

Einsatzlyzer enthält, verwendet oder verbindet Drittanbieter-Komponenten und Datendienste. Für diese gelten weiterhin die jeweiligen Lizenzen, Urheberrechtshinweise und Nutzungsbedingungen.

### Leaflet

- Projekt: Leaflet
- Version: 1.9.4
- Zweck: interaktive Karten
- Lizenz: BSD-2-Clause
- Projektseite: https://leafletjs.com/

### OpenStreetMap

Die Kartendaten stammen von OpenStreetMap-Mitwirkenden. Die erforderliche Quellenangabe wird direkt auf Karten mit OpenStreetMap-Daten eingeblendet.

- Datenquelle: OpenStreetMap-Mitwirkende
- Datenlizenz: Open Database License (ODbL) 1.0
- Copyright- und Attributionshinweise: https://www.openstreetmap.org/copyright

### OpenTopoMap

OpenTopoMap steht optional als topografische Kartenebene zur Verfügung. Bei Verwendung dieser Ebene wird die zugehörige Attribution eingeblendet.

- Kartendaten: © OpenStreetMap-Mitwirkende, ODbL
- Kartendarstellung: © OpenTopoMap-Mitwirkende, CC BY-SA 3.0
- Projektseite: https://opentopomap.org/

### OSRM

Einsatzlyzer kann die Open Source Routing Machine (OSRM) verwenden, um aus gespeicherten Koordinaten Fahrstrecke und geschätzte Fahrzeit zu berechnen. Der öffentliche OSRM-Dienst ist ein externer Dienst und nicht Bestandteil des Plugins.

- Projekt: OSRM Backend
- Lizenz: BSD-2-Clause
- Projekt-Repository: https://github.com/Project-OSRM/osrm-backend

### Karten-Attribution

Einsatzlyzer blendet die jeweils erforderliche Quellenangabe direkt auf der Karte ein. Diese Attribution darf nicht durch Themes, eigenes CSS oder andere Plugins ausgeblendet oder entfernt werden.

Diese Datei ersetzt nicht die vollständigen Lizenztexte oder Nutzungsbedingungen der jeweiligen Projekte und Dienste.

## Einsatzlyzer pure-PHP PDF text extractor

The bundled lightweight extractor in `includes/simple-pdf-text.php` is original Einsatzlyzer code and is distributed under the plugin's GPL-2.0-or-later license. It uses only PHP's standard `iconv` and `zlib` extensions. No external PDF parser package is included.
