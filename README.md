<p align="center">
  <a href="docs/einsatzlyzer-programmvorschau-github.png">
    <img src="docs/einsatzlyzer-programmvorschau-github.png" alt="Einsatzlyzer program preview" width="100%">
  </a>
</p>

# Einsatzlyzer

<p align="center">
  <strong>Modern WordPress incident management for fire departments and emergency services.</strong><br>
  <strong>Moderne WordPress-Einsatzverwaltung für Feuerwehren und Hilfsorganisationen.</strong>
</p>

<p align="center">
  <a href="#english">English</a> · <a href="#deutsch">Deutsch</a> ·
  <a href="CHANGELOG.md">Changelog</a> · <a href="RELEASE_NOTES_10.2.1.md">Release notes</a> ·
  <a href="LICENSE">License</a>
</p>

> **Version 10.2.2 · WordPress Plugin · PHP 8.x · GPL-2.0-or-later**  
> **Copyright © 2026 Ralf Ebert.**

## Screenshots / Bildschirmfotos

### Incident management / Einsatzverwaltung

| Deutsch | English |
|---|---|
| [![Einsatzverwaltung auf Deutsch](docs/screenshots/all-incidents-german.webp)](docs/screenshots/all-incidents-german.webp) | [![Incident management in English](docs/screenshots/all-incidents-english.webp)](docs/screenshots/all-incidents-english.webp) |

### Frontend archive / Einsatzarchiv im Frontend

| Deutsch | English |
|---|---|
| [![Deutsches Einsatzarchiv](docs/screenshots/all-incidents-frontpage-german.webp)](docs/screenshots/all-incidents-frontpage-german.webp) | [![English incident archive](docs/screenshots/all-incidents-frontpage-english.webp)](docs/screenshots/all-incidents-frontpage-english.webp) |

### Incident report / Einsatzbericht

| Deutsch | English |
|---|---|
| [![Deutscher Einsatzbericht](docs/screenshots/incident-summary-german.webp)](docs/screenshots/incident-summary-german.webp) | [![English incident report](docs/screenshots/incident-summary-english.webp)](docs/screenshots/incident-summary-english.webp) |

### Import and export / Import und Export

| Deutsch | English |
|---|---|
| [![Import und Export auf Deutsch](docs/screenshots/import-export-german.webp)](docs/screenshots/import-export-german.webp) | [![Import and export in English](docs/screenshots/import-export-english.webp)](docs/screenshots/import-export-english.webp) |

### Mobile incident management / Mobile Einsatzverwaltung

| Deutsch | English |
|---|---|
| [![Mobile Einsatzverwaltung auf Deutsch](docs/screenshots/all-incidents-mobile-german.webp)](docs/screenshots/all-incidents-mobile-german.webp) | [![Mobile incident management in English](docs/screenshots/all-incidents-mobile-english.webp)](docs/screenshots/all-incidents-mobile-english.webp) |

### Mobile frontend archive / Mobiles Einsatzarchiv im Frontend

| Deutsch | English |
|---|---|
| [![Mobiles deutsches Einsatzarchiv](docs/screenshots/all-incidents-frontpage-mobile-german.webp)](docs/screenshots/all-incidents-frontpage-mobile-german.webp) | [![Mobile English incident archive](docs/screenshots/all-incidents-frontpage-mobile-english.webp)](docs/screenshots/all-incidents-frontpage-mobile-english.webp) |

### Mobile incident report / Mobiler Einsatzbericht

| Deutsch | English |
|---|---|
| [![Mobiler deutscher Einsatzbericht](docs/screenshots/incident-summary-mobile-german.webp)](docs/screenshots/incident-summary-mobile-german.webp) | [![Mobile English incident report](docs/screenshots/incident-summary-mobile-english.webp)](docs/screenshots/incident-summary-mobile-english.webp) |

### Mobile settings / Mobile Einstellungen

| Deutsch | English |
|---|---|
| [![Mobile Einstellungen auf Deutsch](docs/screenshots/settings-mobile-german.webp)](docs/screenshots/settings-mobile-german.webp) | [![Mobile settings in English](docs/screenshots/settings-mobile-english.webp)](docs/screenshots/settings-mobile-english.webp) |

---

<a id="english"></a>

# English

## Purpose of the plugin

**Einsatzlyzer 10.2.2** is an independent WordPress plugin for recording, managing and presenting emergency incident reports. It combines structured data entry in the WordPress administration area with a responsive public incident archive, modern detail pages, image galleries, OpenStreetMap maps, filters, SEO support, and complete import and export tools.

The plugin is designed for fire departments and emergency organizations that want to publish incident reports clearly while retaining full control over their own WordPress website and data.

## Main functions

- Dedicated WordPress post type for incident reports
- Structured fields for alert, incident location, date, time, duration, vehicles, units and report text
- Manual or automatically generated incident numbers
- Featured image and image gallery
- Compact administration overview with status indicators and filters
- Responsive frontend archive with search, year and incident-type filters
- Expandable overview map with markers and clustering
- Modern individual incident pages
- OpenStreetMap and Leaflet without a commercial map API
- Exact, approximate or hidden incident positions
- Complete ZIP backup and restore
- Excel-compatible CSV export
- Import preview with duplicate detection and selectable import strategy
- Support for Yoast SEO, Rank Math and SEOPress
- German and English plugin interface
- Optimized desktop, tablet and mobile display

## Shortcode

Create a public WordPress page and insert:

```text
[ffl_einsatz_liste_komplett]
```

Select this page as the incident archive page in the Einsatzlyzer settings.

## Installation

1. Open **Plugins → Add New → Upload Plugin** in WordPress.
2. Select `einsatzlyzer-10.2.2.zip`.
3. Install and activate the plugin.
4. Open **Einsatzlyzer → Settings**.
5. Enter the organization name and configure the incident archive page.
6. Add the shortcode shown above to the selected public page.

The PHP extension `ZipArchive` is required for ZIP import and export.

## Maps and privacy

Einsatzlyzer uses OpenStreetMap and Leaflet. No Google Maps or HERE API key and no billing account are required. The visitor's location is never requested or stored.

Each incident can use an exact location, an approximate location or no public location. Optional route planning opens externally through OpenStreetMap.

## Import and export

The plugin can create a complete ZIP backup containing incident reports, metadata and selected images. Imports are checked before data is written. Existing records can be skipped, updated or imported as copies.

CSV export is intended for review and processing in spreadsheet programs. ZIP backup is the correct format for complete restoration.

## Plugin language

The plugin interface can be switched independently between German and English. WordPress, the active theme and other plugins keep their own language.

User-entered incident titles, locations, reports and other content are never automatically translated or modified.

## Updating

Existing incident reports and metadata remain intact during a normal plugin update. A complete backup of WordPress files and the database is still recommended before major changes.

## License

Einsatzlyzer is released under **GPL-2.0-or-later**. Third-party components retain their own licences. See [`THIRD_PARTY_NOTICES.md`](THIRD_PARTY_NOTICES.md).

## Author

**Ralf Ebert**

---

<a id="deutsch"></a>

# Deutsch

## Zweck des Plugins

**Einsatzlyzer 10.2.2** ist ein eigenständiges WordPress-Plugin zur Erfassung, Verwaltung und Darstellung von Einsatzberichten. Es verbindet eine strukturierte Datenerfassung im WordPress-Backend mit einem responsiven öffentlichen Einsatzarchiv, modernen Einzelseiten, Bildergalerien, OpenStreetMap-Karten, Filtern, SEO-Unterstützung sowie vollständigen Import- und Exportfunktionen.

Das Plugin richtet sich an Feuerwehren und Hilfsorganisationen, die Einsatzberichte übersichtlich veröffentlichen und dabei die vollständige Kontrolle über ihre eigene WordPress-Website und ihre Daten behalten möchten.

## Hauptfunktionen

- eigener WordPress-Inhaltstyp für Einsatzberichte
- strukturierte Felder für Alarmierung, Einsatzort, Datum, Uhrzeit, Dauer, Fahrzeuge, Einheiten und Bericht
- manuelle oder automatisch erzeugte Einsatznummern
- Beitragsbild und Bildergalerie
- kompakte Backend-Übersicht mit Statusanzeigen und Filtern
- responsives Frontend-Archiv mit Suche sowie Jahres- und Einsatzartfilter
- einblendbare Gesamtkarte mit Markern und Clustern
- moderne Einzelseiten für Einsatzberichte
- OpenStreetMap und Leaflet ohne kommerzielle Karten-API
- genaue, angenäherte oder ausgeblendete Einsatzposition
- vollständige ZIP-Sicherung und Wiederherstellung
- Excel-kompatibler CSV-Export
- Importvorschau mit Duplikaterkennung und wählbarer Importstrategie
- Unterstützung für Yoast SEO, Rank Math und SEOPress
- deutsche und englische Plugin-Oberfläche
- optimierte Darstellung auf Desktop, Tablet und Smartphone

## Shortcode

Eine öffentliche WordPress-Seite anlegen und dort einfügen:

```text
[ffl_einsatz_liste_komplett]
```

Diese Seite anschließend in den Einsatzlyzer-Einstellungen als Einsatzübersicht auswählen.

## Installation

1. In WordPress **Plugins → Installieren → Plugin hochladen** öffnen.
2. `einsatzlyzer-10.2.2.zip` auswählen.
3. Plugin installieren und aktivieren.
4. **Einsatzlyzer → Einstellungen** öffnen.
5. Organisationsname und Einsatzübersichtsseite einrichten.
6. Den oben genannten Shortcode in die ausgewählte öffentliche Seite einfügen.

Für ZIP-Import und ZIP-Export muss die PHP-Erweiterung `ZipArchive` verfügbar sein.

## Karten und Datenschutz

Einsatzlyzer verwendet OpenStreetMap und Leaflet. Ein Google-Maps- oder HERE-API-Schlüssel und ein Abrechnungskonto sind nicht erforderlich. Der Standort des Besuchers wird nicht abgefragt oder gespeichert.

Für jeden Einsatz kann eine genaue, angenäherte oder keine öffentliche Position gewählt werden. Die optionale Routenplanung wird extern über OpenStreetMap geöffnet.

## Import und Export

Das Plugin kann eine vollständige ZIP-Sicherung mit Einsatzberichten, Metadaten und ausgewählten Bildern erstellen. Ein Import wird geprüft, bevor Daten geschrieben werden. Vorhandene Datensätze können übersprungen, aktualisiert oder als Kopie importiert werden.

Der CSV-Export ist zur Kontrolle und Verarbeitung in Tabellenprogrammen vorgesehen. Für eine vollständige Wiederherstellung ist die ZIP-Sicherung das richtige Format.

## Plugin-Sprache

Die Plugin-Oberfläche kann unabhängig zwischen Deutsch und Englisch umgeschaltet werden. WordPress, das aktive Theme und andere Plugins behalten ihre eigene Sprache.

Selbst eingegebene Einsatztitel, Orte, Berichte und andere Inhalte werden niemals automatisch übersetzt oder verändert.

## Aktualisierung

Vorhandene Einsatzberichte und Metadaten bleiben bei einem normalen Plugin-Update erhalten. Vor größeren Änderungen wird trotzdem eine vollständige Sicherung der WordPress-Dateien und Datenbank empfohlen.

## Lizenz

Einsatzlyzer wird unter **GPL-2.0-or-later** veröffentlicht. Drittanbieterbestandteile behalten ihre eigenen Lizenzen. Weitere Hinweise stehen in [`THIRD_PARTY_NOTICES.md`](THIRD_PARTY_NOTICES.md).

## Autor

**Ralf Ebert**
