<p align="center">
  <a href="docs/einsatzlyzer-programmvorschau-github.png">
    <img src="docs/einsatzlyzer-programmvorschau-github.png" alt="Einsatzlyzer program preview" width="100%">
  </a>
</p>

## Program preview / Programmvorschau

<p align="center">
  <a href="docs/screenshots/all-incidents-english.webp"><img src="docs/screenshots/all-incidents-english.webp" alt="Incident archive in English" width="49%"></a>
  <a href="docs/screenshots/all-incidents-german.webp"><img src="docs/screenshots/all-incidents-german.webp" alt="Einsatzarchiv auf Deutsch" width="49%"></a>
</p>

<p align="center">
  <a href="docs/screenshots/all-incidents-frontpage-english.webp"><img src="docs/screenshots/all-incidents-frontpage-english.webp" alt="Front page incident overview in English" width="49%"></a>
  <a href="docs/screenshots/all-incidents-frontpage-german.webp"><img src="docs/screenshots/all-incidents-frontpage-german.webp" alt="Einsatzübersicht auf der Startseite auf Deutsch" width="49%"></a>
</p>

<p align="center">
  <a href="docs/screenshots/incident-summary-english.webp"><img src="docs/screenshots/incident-summary-english.webp" alt="Incident summary in English" width="49%"></a>
  <a href="docs/screenshots/incident-summary-german.webp"><img src="docs/screenshots/incident-summary-german.webp" alt="Einsatzübersicht auf Deutsch" width="49%"></a>
</p>

<p align="center">
  <a href="docs/screenshots/import-export-english.webp"><img src="docs/screenshots/import-export-english.webp" alt="Import and export in English" width="49%"></a>
  <a href="docs/screenshots/import-export-german.webp"><img src="docs/screenshots/import-export-german.webp" alt="Import und Export auf Deutsch" width="49%"></a>
</p>

<p align="center">
  <a href="docs/screenshots/all-incidents-mobile-english.webp"><img src="docs/screenshots/all-incidents-mobile-english.webp" alt="Mobile incident archive in English" width="24%"></a>
  <a href="docs/screenshots/all-incidents-mobile-german.webp"><img src="docs/screenshots/all-incidents-mobile-german.webp" alt="Mobiles Einsatzarchiv auf Deutsch" width="24%"></a>
  <a href="docs/screenshots/all-incidents-frontpage-mobile-english.webp"><img src="docs/screenshots/all-incidents-frontpage-mobile-english.webp" alt="Mobile front page incident overview in English" width="24%"></a>
  <a href="docs/screenshots/all-incidents-frontpage-mobile-german.webp"><img src="docs/screenshots/all-incidents-frontpage-mobile-german.webp" alt="Mobile Einsatzübersicht auf der Startseite auf Deutsch" width="24%"></a>
</p>

<p align="center">
  <a href="docs/screenshots/incident-summary-mobile-english.webp"><img src="docs/screenshots/incident-summary-mobile-english.webp" alt="Mobile incident summary in English" width="24%"></a>
  <a href="docs/screenshots/incident-summary-mobile-german.webp"><img src="docs/screenshots/incident-summary-mobile-german.webp" alt="Mobile Einsatzübersicht auf Deutsch" width="24%"></a>
  <a href="docs/screenshots/settings-mobile-english.webp"><img src="docs/screenshots/settings-mobile-english.webp" alt="Mobile settings in English" width="24%"></a>
  <a href="docs/screenshots/settings-mobile-german.webp"><img src="docs/screenshots/settings-mobile-german.webp" alt="Mobile Einstellungen auf Deutsch" width="24%"></a>
</p>

---

# Einsatzlyzer

**Modern WordPress incident management for fire departments and emergency services.**  
**Moderne Einsatzverwaltung für WordPress – entwickelt für Feuerwehren und Hilfsorganisationen.**

[English](#english) · [Deutsch](#deutsch) · [Changelog](CHANGELOG.md) · [Release notes](RELEASE_NOTES_10.2.0.md) · [License](LICENSE)

**Version 10.2.0 · WordPress · PHP 8.x · GPL-2.0-or-later**

---

# English

Einsatzlyzer is a standalone WordPress plugin for recording, managing and presenting emergency incident reports. It combines structured data entry in the WordPress admin area with a responsive incident archive, modern detail pages, image galleries, OpenStreetMap maps, filters, SEO support, and complete import and export tools.

## Main features

### Incident management in WordPress

- Dedicated public post type for incident reports
- Structured fields for alarm time, incident duration, location, keyword, report, vehicles and participating units
- Incident location selection directly on a map
- Manual or automatically generated incident numbers
- Featured image and image gallery
- Image credits, external links and additional incident information
- Compact admin overview with thumbnails and status indicators
- Filters by year, incident type and missing information
- Extended search by location, keyword and incident number

### Frontend incident archive

- Responsive card and map layout
- Search by keyword, location and report text
- Filters by year and incident type
- Pagination for large archives
- Expandable overview map with markers and clusters
- Live filters without a complete page reload

Shortcode:

```text
[ffl_einsatz_liste_komplett]
```

### Individual incident reports

- Modern image header
- Incident data, summary and full report
- Participating vehicles and units
- Image gallery with lightbox
- Incident location map
- Related incidents
- Sharing via WhatsApp, Facebook, email and direct link
- Optimized display on desktop, tablet and smartphone

### Maps and privacy

- OpenStreetMap and Leaflet
- No Google Maps or HERE API key required
- No billing account required
- Exact, approximate or hidden incident position per report
- Optional straight-line distance from the fire station
- External route planning via OpenStreetMap
- Visitor location is never requested or stored

### Import and export

- Complete ZIP export of incident reports and images
- Backup of featured images, galleries and embedded report images
- Technical verification using SHA-256 checksums
- Duplicate detection using stable identifiers and fingerprints
- Import preview with skip, update or copy strategies
- Optional pre-import backup and rollback of an import session
- Excel-compatible CSV export
- Import log as a text file

### Search engines and social media

- Public, directly accessible incident reports
- Canonical URL and meta description
- Open Graph and social media preview images
- Support for Yoast SEO, Rank Math and SEOPress
- Incident reports use `NewsArticle` structured data instead of Event schema
- Structured HTML output
- No built-in sitemap; incident URLs can be added by a separate SEO or sitemap plugin

### Theme and Elementor integration

- Uses the normal header and footer of the active WordPress theme
- Optional saved Elementor templates for incident detail pages
- Elementor Pro is not required
- Existing pages, posts and Theme Builder conditions remain untouched

## Installation

1. In WordPress, open **Plugins → Add New → Upload Plugin**.
2. Select `einsatzlyzer-10.2.0.zip`.
3. Install and activate the plugin.
4. Open **Einsatzlyzer → Settings** and enter the organization, archive page and, if required, the fire station location.
5. Create a public WordPress page and insert `[ffl_einsatz_liste_komplett]`.
6. Select this page as the incident archive page in the Einsatzlyzer settings.

The PHP extension `ZipArchive` must be available for ZIP import and export.

## Updating

Existing incident reports and metadata remain intact during a normal plugin update. A complete backup of WordPress files and the database is still recommended before major changes.

## Plugin language

Einsatzlyzer can be switched independently between German and English in the plugin settings. WordPress, the active theme and other plugins keep their own language. User-entered titles, locations and report texts are never automatically translated or modified.

## Version history

The complete history is available in [CHANGELOG.md](CHANGELOG.md).

## License

Einsatzlyzer is released under **GPL-2.0-or-later**. Third-party components retain their own licenses. See [THIRD_PARTY_NOTICES.md](THIRD_PARTY_NOTICES.md).

## Author

**Ralf Ebert**

---

# Deutsch

Einsatzlyzer ist ein eigenständiges WordPress-Plugin zur Erfassung, Verwaltung und Darstellung von Einsatzberichten. Es verbindet eine strukturierte Datenerfassung im WordPress-Backend mit einem responsiven Einsatzarchiv, modernen Einzelseiten, Bildergalerien, OpenStreetMap-Karten, Filtern, SEO-Unterstützung sowie vollständigem Import und Export.

## Hauptfunktionen

### Einsatzverwaltung im Backend

- eigener öffentlicher Inhaltstyp für Einsatzberichte
- strukturierte Eingabe von Alarmierung, Einsatzzeiten, Einsatzort, Stichwort, Bericht, Fahrzeugen und beteiligten Einheiten
- Einsatzort direkt auf einer Karte auswählen
- manuelle oder automatisch erzeugte Einsatznummer
- Beitragsbild und Bildergalerie
- Bildquelle, weiterführende Links und zusätzliche Einsatzinformationen
- kompakte Einsatzübersicht mit Vorschaubildern und Statusanzeigen
- Filter nach Jahr, Einsatzart und fehlenden Angaben
- erweiterte Suche nach Ort, Stichwort und Einsatznummer

### Einsatzarchiv im Frontend

- responsives Karten- und Kachellayout
- Suche nach Stichwort, Ort und Bericht
- Filter nach Jahr und Einsatzart
- Seitennavigation für umfangreiche Archive
- einblendbare Gesamtkarte mit Einsatzmarkern und Clustern
- aktive Filter ohne vollständiges Neuladen der Seite

Verwendeter Shortcode:

```text
[ffl_einsatz_liste_komplett]
```

### Einzelne Einsatzberichte

- moderner Bild-Kopfbereich
- Einsatzdaten, Kurzfassung und ausführlicher Bericht
- beteiligte Fahrzeuge und Einheiten
- Bildergalerie mit Lightbox
- Karte des Einsatzortes
- verwandte Einsätze
- Teilen über WhatsApp, Facebook, E-Mail und Link
- optimierte Darstellung auf Desktop, Tablet und Smartphone

### Karten und Datenschutz

- OpenStreetMap und Leaflet
- kein Google-Maps- oder HERE-API-Schlüssel erforderlich
- kein Abrechnungskonto notwendig
- genaue, angenäherte oder ausgeblendete Einsatzposition je Bericht
- optionale Luftlinienentfernung vom Feuerwehrhaus
- externe Routenplanung über OpenStreetMap
- der Standort des Besuchers wird nicht abgefragt oder gespeichert

### Import und Export

- vollständiger ZIP-Export von Einsatzberichten und Bildern
- Sicherung von Beitragsbildern, Galerien und eingebetteten Berichtsbildern
- technische Prüfung durch SHA-256-Prüfsummen
- Duplikaterkennung über stabile Kennungen und Fingerabdrücke
- Importvorschau mit Strategien zum Überspringen, Aktualisieren oder Kopieren
- optionales Vorab-Backup und Rücksetzung einer Import-Sitzung
- Excel-kompatibler CSV-Export
- Importprotokoll als Textdatei

### Suchmaschinen und Social Media

- öffentliche, direkt aufrufbare Einsatzberichte
- Canonical-URL und Meta-Beschreibung
- Open-Graph- und Social-Media-Vorschaubilder
- Unterstützung für Yoast SEO, Rank Math und SEOPress
- Einsatzberichte werden als `NewsArticle` statt als Veranstaltung ausgezeichnet
- strukturierte HTML-Ausgabe
- keine eigene Sitemap; Einsatz-URLs können durch ein separates Sitemap- oder SEO-Plugin aufgenommen werden

### Theme- und Elementor-Integration

- normaler Header und Footer des aktiven WordPress-Themes
- optionale gespeicherte Elementor-Vorlagen für Einsatz-Einzelseiten
- Elementor Pro ist dafür nicht erforderlich
- bestehende Seiten, Beiträge und Theme-Builder-Bedingungen bleiben unberührt

## Vorschaubilder

Die Vorschaubilder stehen im englischen Abschnitt weiter oben und zeigen jeweils die deutsche und englische Plugin-Darstellung.

## Installation

1. In WordPress **Plugins → Installieren → Plugin hochladen** öffnen.
2. Die Datei `einsatzlyzer-10.2.0.zip` auswählen.
3. Plugin installieren und aktivieren.
4. Unter **Einsatzlyzer → Einstellungen** die Organisation, die Einsatzübersichtsseite und bei Bedarf den Standort des Feuerwehrhauses eintragen.
5. Eine öffentliche WordPress-Seite anlegen und dort `[ffl_einsatz_liste_komplett]` einfügen.
6. Diese Seite anschließend in den Einsatzlyzer-Einstellungen als Einsatzübersicht auswählen.

Für den ZIP-Import und -Export muss auf dem Server die PHP-Erweiterung `ZipArchive` verfügbar sein.

## Aktualisierung

Vorhandene Einsatzberichte und ihre Metadaten bleiben bei einem normalen Plugin-Update erhalten. Vor größeren Änderungen wird trotzdem ein vollständiges Backup der WordPress-Dateien und Datenbank empfohlen.

## Plugin-Sprache

Einsatzlyzer kann in den Plugin-Einstellungen unabhängig zwischen Deutsch und Englisch umgeschaltet werden. WordPress, das aktive Theme und andere Plugins behalten ihre eigene Sprache. Selbst eingegebene Titel, Orte und Berichtstexte werden niemals automatisch übersetzt oder verändert.

## Versionsgeschichte

Die vollständige Übersicht steht in [CHANGELOG.md](CHANGELOG.md).

## Lizenz

Einsatzlyzer wird unter **GPL-2.0-or-later** veröffentlicht. Drittanbieterbestandteile behalten ihre eigenen Lizenzen. Weitere Hinweise stehen in [THIRD_PARTY_NOTICES.md](THIRD_PARTY_NOTICES.md).

## Autor

**Ralf Ebert**