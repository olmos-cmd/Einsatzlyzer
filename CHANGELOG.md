## 10.6.14

### Deutsch
- Quellangaben der fünf weiterführenden Links ausdrücklich in den vollständigen Sicherungsvertrag aufgenommen.
- Sicherungsschema auf Version 7 erhöht.
- URLs und Quellangaben werden beim vollständigen ZIP-Export gemeinsam gesichert und beim Import unverändert wiederhergestellt.
- Regressionstest für die Link-/Quellenfelder ergänzt.

### English
- Added the five external-link source labels explicitly to the full backup contract.
- Raised the backup schema to version 7.
- URLs and source labels are backed up together and restored unchanged by the full ZIP import.
- Added a regression test for external-link URL/source fields.

## 10.6.13

### Deutsch
- Fahrstreckenberechnung für Einsatzstellen unter 100 Metern Entfernung vom Feuerwehrhaus korrigiert; statt einer ungültigen OSRM-Route wird eine lokale Näherung gespeichert.
- Fehlerliste der Stapelberechnung zeigt jetzt Einsatztitel und einen direkten Link zum Bearbeiten des betroffenen Einsatzes.
- Weiterführende Links um eine separate Quellangabe erweitert.
- Quellangaben werden im öffentlichen Einsatzbericht angezeigt und durch den vollständigen ZIP-Import/-Export mitgesichert.

### English
- Fixed route calculation for incidents within 100 metres of the fire station; a local approximation is stored instead of an invalid OSRM route.
- Batch routing errors now show the incident title and a direct edit link.
- Added a separate source field for further links.
- Link sources are shown in the public incident report and included in complete ZIP backup/restore.

## 10.6.12

### English
- Linked the archive footer logo to the official Einsatzlyzer GitHub repository.
- Updated the GitHub program preview and screenshot gallery.
- Added accessible hover and focus behavior for the footer logo link.

### Deutsch
- Archiv-Footer-Logo mit dem offiziellen Einsatzlyzer-GitHub-Repository verlinkt.
- GitHub-Programmvorschau und Bildschirmfotos aktualisiert.
- Barrierefreies Hover- und Fokusverhalten für den Logo-Link ergänzt.

# 10.6.12

- Vollständigen Sicherungsvertrag für Entfernungs- und Routingdaten ergänzt.
- Sicherungsschema auf Version 6 erhöht.
- Luftlinie, Fahrstrecke, Fahrzeit, Routinganbieter, Berechnungszeitpunkt, Start-/Zielkoordinaten und Routingfehler werden ausdrücklich exportiert und wiederhergestellt.
- Globale Anzeigeart und Feuerwehrhaus-/Standardkoordinaten werden ausdrücklich gesichert und nach dem Import geprüft.
- Komplette englische Übersetzung des Entfernungsbereichs, der Einzelberechnung und der Stapelverarbeitung vervollständigt.

# 10.6.10

- Fahrstrecke und Fahrzeit können direkt im einzelnen Einsatz berechnet und neu berechnet werden.
- Gespeicherte Entfernungswerte lassen sich im Einsatzeditor löschen.
- Anzeige von Luftlinie, Fahrstrecke, Fahrzeit, Berechnungszeitpunkt und verwendeten Koordinaten.
- Klare Fehlermeldungen bei fehlenden oder ungültigen Koordinaten.

# 10.6.9

- Depeschen-Auswertung sammelt alle Funkrufnamen einer Ressourcenzeile.
- Alle bekannten Fahrzeuge werden in der Importvorschau angezeigt.
- Beim Übernehmen werden alle erkannten Fahrzeug-IDs mit dem Einsatz verknüpft.
- Unbekannte Funkrufnamen bleiben getrennt sichtbar.
- Dubletten werden entfernt; vorhandene Zuordnungen können ergänzt oder ersetzt werden.

# 10.6.7

- Fahrzeugverwaltung auf Mobilgeräten als übersichtliche Kartenansicht dargestellt.
- Filter nach Gemeinde, Ortswehr, eigener/externer Zuordnung und Status ergänzt.
- Fahrzeugauswahl im Einsatzeditor mit Suche, Gemeinde-Filtern und Bereich für ausgewählte Fahrzeuge überarbeitet.
- Standardmäßig werden nur eigene und bereits ausgewählte Fahrzeuge angezeigt.
- Korrigierte Fahrzeug-Importdatei für Jümme und Uplengen erstellt.

# Changelog

## 10.6.4 – Depeschen-Import im geöffneten Einsatz korrigiert

### Deutsch
- Ungültig verschachtelte Formulare im WordPress-Einsatzeditor entfernt.
- PDF-Auswahl und Importvorschau verwenden nun eigenständige Formulare außerhalb des WordPress-Beitragsformulars.
- „Depesche prüfen“ behält die ID des geöffneten Einsatzes zuverlässig bei.
- Die Vorschau bleibt beim geöffneten Einsatz und springt nicht mehr zur Beitragsübersicht.
- Übernehmen, neuen Entwurf anlegen und Abbrechen funktionieren weiterhin auch in der zentralen Importseite.

### English
- Removed invalid nested forms from the WordPress incident editor.
- PDF selection and import preview now use standalone forms outside the WordPress post form.
- “Review Dispatch” reliably preserves the currently opened incident ID.
- The preview remains in the open incident instead of redirecting to the regular posts list.
- Apply, create draft and cancel remain available in the central import page.

## 10.6.4 – Sicherer Depeschen-Import und vertrauliche Leitstellenkennung

### Deutsch
- Die Depeschenprüfung bleibt beim geöffneten Einsatz und zeigt die Vorschau direkt im Einsatzeditor.
- Leitstellen-Einsatznummern werden niemals in sichtbare Einsatzfelder übernommen.
- Die Kennung erscheint in der Vorschau nur maskiert.
- Intern wird ausschließlich ein nicht rückrechenbarer Prüfwert gespeichert.
- Frühere Klartextwerte werden einmalig migriert und gelöscht.
- Frontend, Jahresberichte sowie öffentliche PDF-, CSV- und XLSX-Ausgaben enthalten keine Leitstellenkennung.
- Vollsicherungen enthalten nur den technischen Hash und keinen ursprünglichen Dateinamen mit der Kennung.

### English
- Dispatch review remains attached to the open incident and is displayed directly in the incident editor.
- Control-center incident identifiers are never imported into visible incident fields.
- The identifier is masked in the preview.
- Only a non-reversible verification hash is stored internally.
- Older clear-text values are migrated once and deleted.
- Frontend, annual reports and public PDF, CSV and XLSX outputs never contain the identifier.
- Full backups contain only the technical hash and no original filename carrying the identifier.

## 10.6.2 – PDF-Import ohne Serverabhängigkeit

### Deutsch
- Reinen PHP-PDF-Textparser direkt in Einsatzlyzer integriert.
- `pdftotext` ist nicht mehr erforderlich und dient nur noch als optionaler Fallback.
- Textbasierte Einsatzdepeschen funktionieren damit auch auf gewöhnlichem Shared Hosting.
- Klare Meldung für reine Scan-PDFs ohne maschinenlesbaren Text ergänzt.

### English
- Bundled a pure-PHP PDF text extractor directly with Einsatzlyzer.
- `pdftotext` is no longer required and remains an optional fallback only.
- Text-based dispatch PDFs now work on normal shared hosting.
- Added a clear message for scanned PDFs without machine-readable text.

## 10.6.2 – Transparenter Bezugsort der Wetterdaten

### Deutsch
- Wetterhinweis im Frontend unterscheidet jetzt zwischen Einsatzstelle und Feuerwehrhaus/Standardstandort.
- Bei echten Einsatzkoordinaten erscheint: „Datenquelle: Open-Meteo · Bezugsort: Einsatzstelle“.
- Bei Nutzung des Standardstandorts werden dessen Bezeichnung und der abweichende Bezugsort ausdrücklich genannt.
- Dieselbe transparente Anzeige wurde im Einsatzeditor ergänzt.

### English
- Weather notices now distinguish between incident coordinates and the fire-station/default location.
- Incident coordinates are labelled as the incident location.
- When the default location is used, its configured name and the differing reference location are shown explicitly.
- The same transparent source information is available in the incident editor.

## 10.6.0 – Fahrzeugverwaltung und sicherer Depeschen-Import

### Deutsch
- Neuer Menüpunkt „Fahrzeuge“ mit Funkrufname, Fahrzeugbezeichnung, Ortswehr, eigener/externer Zuordnung, Aktivstatus und optionalem Bild.
- Neuer Menüpunkt „Depesche importieren“ sowie Importbox direkt im Einsatzeditor.
- PDF-Daten werden zuerst in einer Vorschau verglichen; vorhandene Felder werden niemals blind überschrieben.
- Interne Einsatznummer und Leitstellen-Einsatznummer werden getrennt gespeichert.
- Eigene Fahrzeuge werden ausschließlich über zuvor gepflegte Funkrufnamen erkannt; unbekannte Funkrufnamen werden nicht automatisch zugeordnet.
- Vollständiger ZIP-Import und -Export erweitert um Fahrzeuge, Funkrufnamen, Fahrzeugbilder und Depeschen-Metadaten.

### English
- Added a Vehicles menu with call sign, designation, station, own/external assignment, active status and optional image.
- Added a Dispatch PDF import center and an import box in the incident editor.
- Imported values are reviewed before saving; existing fields are never overwritten blindly.
- Internal incident numbers and control-center incident numbers are stored separately.
- Own vehicles are matched only through user-maintained call signs; unknown call signs are never assigned automatically.
- Full ZIP backup and restore now includes vehicles, call signs, vehicle images and dispatch metadata.

## 10.5.5 – Cleaner administration and one-time cache notice

### Deutsch
- Einmaligen, projektspezifischen Menüpunkt „Werkzeuge“ samt Standardhinweis-Bereinigung vollständig entfernt.
- Import verändert Einsatztexte nicht mehr durch eine spezielle Altbestands-Bereinigung.
- Cache-Hinweis erscheint nur noch einmal nach einem Update und ausschließlich auf Einsatzlyzer-Seiten.
- „Alle Caches wurden geleert“ durch die eindeutige Aktion „Später“ ersetzt.
- Nach erfolgreichem Leeren erscheint eine kurze Erfolgsmeldung; anschließend bleibt der Update-Hinweis ausgeblendet.
- Cache-Erkennung zeigt nur noch konkret erkannte Cache-Plugins statt eines allgemeinen WordPress-Seitencache-Hinweises.
- Dauerhafte manuelle Cache-Funktion bleibt auf der Diagnoseseite verfügbar.

### English
- Removed the one-off project-specific Tools menu and legacy standard-notice cleanup completely.
- Imports no longer mutate incident text through a special legacy cleanup rule.
- The cache notice now appears once after an update and only on Einsatzlyzer screens.
- Replaced the ambiguous “All caches have been cleared” action with “Later”.
- A short success notice is shown after clearing; the update notice then remains dismissed.
- Cache detection now lists only specifically detected cache plugins instead of a generic WordPress page-cache label.
- Manual cache clearing remains available on the Diagnostics page.

## 10.5.4 – Clear diagnostic results and filters

- Added a labelled diagnostic result section with current page, total pages and visible incident range.
- Added 25, 50, 100 and all-items page-size options.
- Added search and quick filters for quality, weather, images, location, SEO and schema.
- Added compact quality totals and a mobile-friendly results table.

## 10.5.4 – Reliable PDF, CSV and XLSX annual-report exports

### English
- Replaced direct output downloads with protected server-side file generation.
- Added visible success and error messages for PDF, CSV and XLSX.
- Added secure one-hour download links and cleanup after download.
- Added a browser print view as a PDF fallback.
- Added explicit checks for writable directories, ZipArchive and generated file size.

### Deutsch
- Direkte Ausgaben durch eine geschützte serverseitige Dateierstellung ersetzt.
- Sichtbare Erfolgs- und Fehlermeldungen für PDF, CSV und XLSX ergänzt.
- Sichere Download-Links mit einer Gültigkeit von einer Stunde sowie Löschung nach dem Download ergänzt.
- Browser-Druckansicht als PDF-Rückfallebene ergänzt.
- Prüfungen für Schreibrechte, ZipArchive und Dateigröße ergänzt.

## 10.5.2 – Verified complete weather import and export

### English

- Raised the complete backup schema to Version 3.
- Explicitly documented default-location settings, incident weather metadata and weather payload fields in the backup manifest.
- Ensured fire-station/default-location name and coordinates are exported even when WordPress has not yet persisted the options.
- Added post-import verification for restored weather location settings while keeping older backups compatible.
- Added a regression test for the complete weather backup contract.

### Deutsch

- Schema der vollständigen Sicherung auf Version 3 erhöht.
- Standardstandort-Einstellungen, Wetter-Metadaten der Einsätze und Felder des Wetterdatensatzes ausdrücklich im Manifest dokumentiert.
- Feuerwehrhaus-/Standardstandort mit Name und Koordinaten wird auch dann exportiert, wenn WordPress die Optionen bisher noch nicht physisch gespeichert hatte.
- Kontrolle der wiederhergestellten Wetter-Standorteinstellungen nach dem Import ergänzt; ältere Sicherungen bleiben kompatibel.
- Regressionstest für den vollständigen Wetter-Sicherungsvertrag ergänzt.

## 10.5.1 – Universal weather coordinate fallback

### Deutsch
- Vorhandene gültige Einsatzkoordinaten werden für historische Wetterdaten immer bevorzugt.
- Fehlen Einsatzkoordinaten, wird der frei konfigurierbare Feuerwehrhaus-/Standardstandort verwendet.
- Die verwendete Koordinatenquelle wird mit den Wetterdaten gespeichert und im Backend angezeigt.
- Fest eingebaute Jümme-, A28- und regionale Geocoding-Sonderlogik wurde entfernt.
- Wetter-Stapelabruf funktioniert für alle Feuerwehren mit einem einmalig hinterlegten Standardstandort.

### English
- Valid incident coordinates always take priority for historical weather data.
- If incident coordinates are missing, the configurable fire-station/default location is used.
- The coordinate source is stored with the weather data and shown in the backend.
- Hard-coded Jümme, A28 and regional geocoding logic was removed.
- Batch weather retrieval now works for every fire department after configuring one default location.

## 10.5.0 – Intelligent Jümme location resolution and weather validation

### Deutsch
- Fehlende Koordinaten werden beim Wetterabruf automatisch aus dem Einsatzort ermittelt und gespeichert.
- Schreibweisen wie A28, BAB 28, BAB A28, Bundesautobahn 28 und Bundesautobahn A28 werden vereinheitlicht.
- K-, L- und B-Straßenbezeichnungen werden normalisiert; externe Ortssuche ist auf das Einsatzgebiet Jümme begrenzt.
- Wetter-Stapelabruf verarbeitet nun auch alte Berichte mit Einsatzort, aber ohne Koordinaten.
- 0/0-Koordinaten, Treffer außerhalb der Region und offensichtlich unplausible Wetterwerte werden abgewiesen.
- Wetterdaten speichern verwendete Koordinaten, Ortsquelle, Suchbegriff, Zeitzone und Abruf-URL zur Diagnose.

### English
- Missing coordinates are now resolved and stored automatically from the incident location during weather retrieval.
- Common A28 spellings are normalized, road identifiers are standardized, and external geocoding is bounded to the Jümme response area.
- Batch weather retrieval now supports historical reports with a location but without coordinates.
- Invalid 0/0 coordinates, out-of-area matches and clearly implausible weather values are rejected.

## 10.4.9 – 2026-08-01

### Deutsch
- Die bisher verteilten Status-, SEO-, Bild-, Schema- und Indexierungsspalten wurden durch eine kompakte Qualitätsampel ersetzt.
- Jeder Einsatz wird als „Bereit“, „Fast fertig“ oder „Unvollständig“ bewertet.
- Ein aufklappbarer Detailbereich zeigt Bericht, Bild, Ort, Kurzbericht, SEO/Indexierung, strukturierte Daten und Wetter einzeln an.
- Neue Qualitätsfilter und übersichtliche Kennzahlen für fehlende Bilder, Wetterdaten und Einsatzorte ergänzt.
- Die Einsatzübersicht ist auf Desktop und Mobilgeräten deutlich lesbarer.

### English
- Replaced the fragmented status, SEO, image, schema and indexing columns with one compact quality indicator.
- Each incident is classified as Ready, Almost ready or Incomplete.
- Expandable details show report, image, location, summary, SEO/indexing, structured data and weather checks.
- Added quality filters and overview cards for missing images, weather data and locations.
- Improved readability on desktop and mobile administration screens.

# Changelog

## 10.4.8 – 2026-08-01

### Deutsch
- Früher automatisch ergänzten Satz aus neuen Importen entfernt.
- Neues Werkzeug zum sicheren Entfernen des exakt festgelegten Satzes aus allen bestehenden Einsatzberichten ergänzt.
- Bereinigung erfasst Bericht, Kurzfassung und Textauszug; alle übrigen Inhalte bleiben unverändert.

### English
- Removed the former automatically appended sentence from new imports.
- Added a safe tool that removes only the exact predefined sentence from all existing incident reports.
- Cleanup covers report content, summary and excerpt while leaving all other content unchanged.

## 10.4.8 – 2026-08-01

### English
- Added the existing Einsatzlyzer icon to the installed plugins list for faster recognition.
- Expanded the plugin description to include weather, timeline, maps, galleries, SEO and complete import/export support.
- Kept the plugin-list branding local and dependency-free.

### Deutsch
- Vorhandenes Einsatzlyzer-Symbol zur Liste der installierten Plugins hinzugefügt.
- Plugin-Beschreibung um Wettermodul, Zeitachse, Karten, Galerien, SEO sowie vollständigen Import und Export erweitert.
- Branding der Plugin-Liste bleibt lokal und benötigt keine externen Dateien.

## 10.4.6 – 2026-08-01

### Deutsch
- Historisches Wetter als eigenständiges Dashboard mit Übersicht, Stapelabruf, Fehlern, Statistiken und Einstellungen ausgebaut.
- Dauerhafte Fehlerregistrierung ergänzt; fehlgeschlagene Einsätze bleiben nach Seitenwechsel sichtbar und einzeln wiederholbar.
- Aktionsbereich, Fortschritt, Statuskarten, Suche, Filter und mobile Einsatzkarten überarbeitet.

### English
- Expanded historical weather into a dedicated dashboard with overview, batch fetch, errors, statistics and settings.
- Added persistent error tracking; failed incidents remain visible after reload and can be retried individually.
- Improved actions, progress display, status cards, search, filters and mobile incident cards.


## 10.4.5 – Vollständiger verlustfreier Import und Export

### Deutsch
- Der vollständige ZIP-Export sichert jetzt sämtliche Einsatzlyzer-Metafelder einschließlich Wetter, Zeitachse, Kurz- und Langbericht, Druckdaten, SEO-Status sowie manueller und ausgeschlossener verwandter Einsätze.
- Alle dauerhaften Einsatzlyzer-Einstellungen werden in `einstellungen.json` gesichert und auf Wunsch wiederhergestellt.
- Verknüpfte Einsätze werden über stabile UUIDs exportiert und nach dem Import auf die neuen WordPress-IDs aufgelöst.
- Kommentare samt Kommentar-Metadaten werden vollständig exportiert und importiert.
- Bilddateien, Rollen, Reihenfolge, Alt-Texte, Beschreibungen und benutzerdefinierte Anhang-Metadaten bleiben erhalten.
- Exportformat auf Schema-Version 2 angehoben; ältere Sicherungen bleiben importierbar.

### English
- Full ZIP backups now include all Einsatzlyzer incident fields, plugin settings, relationships, comments, images and custom attachment metadata.
- Related incidents are restored through stable UUID mapping.
- Backup schema version increased to 2 while older backups remain supported.

## 10.4.4 – Mobile historical-weather cards

### English
- Replaced the historical-weather table with full-width cards on mobile devices.
- Added clear fields for alert time, coordinates and weather status.
- Added a full-width weather fetch button to every mobile card.
- Kept the existing desktop table unchanged.
- Improved mobile batch controls and progress display.

### Deutsch
- Die Tabelle für historische Wetterdaten wird auf Mobilgeräten durch vollbreite Einsatzkarten ersetzt.
- Alarmierungszeit, Koordinaten und Wetterstatus werden klar getrennt angezeigt.
- Jede mobile Karte besitzt einen breiten Wetter-Abrufbutton.
- Die bestehende Desktop-Tabelle bleibt unverändert erhalten.
- Stapelsteuerung und Fortschrittsanzeige wurden für Mobilgeräte verbessert.

# Changelog

## 10.4.6 – 2026-08-01

### Deutsch
- Historisches Wetter als eigenständiges Dashboard mit Übersicht, Stapelabruf, Fehlern, Statistiken und Einstellungen ausgebaut.
- Dauerhafte Fehlerregistrierung ergänzt; fehlgeschlagene Einsätze bleiben nach Seitenwechsel sichtbar und einzeln wiederholbar.
- Aktionsbereich, Fortschritt, Statuskarten, Suche, Filter und mobile Einsatzkarten überarbeitet.

### English
- Expanded historical weather into a dedicated dashboard with overview, batch fetch, errors, statistics and settings.
- Added persistent error tracking; failed incidents remain visible after reload and can be retried individually.
- Improved actions, progress display, status cards, search, filters and mobile incident cards.
 / Versionsverlauf

## 10.4.2 – 2026-08-01

### English
- Added automatic historical-weather batch processing for hundreds of incident reports.
- Added separate modes for missing weather data, refreshing all data and retrying failed requests.
- Added progress display, pause, resume, stop and an error log.
- Incidents are processed sequentially to avoid PHP and web-server timeouts.
- Existing weather data is skipped in missing-only mode; reports without alert time or coordinates are excluded automatically.

### Deutsch
- Automatischen Stapelabruf historischer Wetterdaten für mehrere hundert Einsatzberichte ergänzt.
- Getrennte Modi für fehlende Wetterdaten, vollständige Aktualisierung und erneuten Versuch fehlgeschlagener Abrufe ergänzt.
- Fortschrittsanzeige, Pause, Fortsetzen, Beenden und Fehlerprotokoll ergänzt.
- Einsätze werden nacheinander verarbeitet, damit keine PHP- oder Webserver-Timeouts entstehen.
- Im Modus für fehlende Daten werden vorhandene Wetterwerte übersprungen; Berichte ohne Alarmierungszeit oder Koordinaten werden automatisch ausgeschlossen.

## 10.4.1 – 2026-08-01

### English
- Added configurable print output for logo, images, historical weather, timeline, vehicles/units and internal details.
- Added a dedicated annual report page in the WordPress backend.
- Added annual report downloads as PDF, CSV and XLSX.
- Annual exports include incident totals, incident hours, categories and a complete chronological incident list.

### Deutsch
- Konfigurierbare Druckausgabe für Logo, Bilder, historische Wetterdaten, Zeitachse, Fahrzeuge/Einheiten und interne Details ergänzt.
- Eigene Jahresbericht-Seite im WordPress-Backend ergänzt.
- Jahresberichte können als PDF, CSV und XLSX heruntergeladen werden.
- Die Exporte enthalten Einsatzzahl, Einsatzstunden, Kategorien und eine vollständige chronologische Einsatzliste.

## 10.4.0 – 2026-08-01

### English
- Added annual incident statistics through the `[ffl_jahresstatistik]` shortcode, including incident types, deployment hours, vehicle usage and comparison with the previous year.
- Clarified the existing short summary and detailed report workflow.
- Retained and documented the incident timeline with chronological events.
- Added historical weather retrieval from Open-Meteo for the stored alert time and coordinates, including a dedicated bulk overview for older reports.
- Added related-incident scoring based on incident type, keyword, location and title terms, with manual inclusion and exclusion controls.
- Added a clean print view that hides navigation, interactive maps and sharing controls.

### Deutsch
- Jahresstatistik über den Shortcode `[ffl_jahresstatistik]` ergänzt: Einsatzarten, Einsatzstunden, Fahrzeugeinsätze und Vorjahresvergleich.
- Vorhandene Trennung aus Kurzfassung und ausführlichem Bericht klarer eingebunden und dokumentiert.
- Einsatzverlauf als chronologische Zeitleiste beibehalten und dokumentiert.
- Historische Wetterdaten von Open-Meteo anhand gespeicherter Alarmierungszeit und Koordinaten ergänzt, einschließlich Stapelübersicht für alte Berichte.
- Verwandte Einsätze werden nach Einsatzart, Stichwort, Ort und Titelbegriffen bewertet; manuelle Ein- und Ausschlüsse sind möglich.
- Saubere Druckansicht ergänzt, die Navigation, interaktive Karten und Teilen-Funktionen ausblendet.

# Changelog

## 10.4.6 – 2026-08-01

### Deutsch
- Historisches Wetter als eigenständiges Dashboard mit Übersicht, Stapelabruf, Fehlern, Statistiken und Einstellungen ausgebaut.
- Dauerhafte Fehlerregistrierung ergänzt; fehlgeschlagene Einsätze bleiben nach Seitenwechsel sichtbar und einzeln wiederholbar.
- Aktionsbereich, Fortschritt, Statuskarten, Suche, Filter und mobile Einsatzkarten überarbeitet.

### English
- Expanded historical weather into a dedicated dashboard with overview, batch fetch, errors, statistics and settings.
- Added persistent error tracking; failed incidents remain visible after reload and can be retried individually.
- Improved actions, progress display, status cards, search, filters and mobile incident cards.


## 10.4.3 – 2026-08-01

### English
- Compact mobile incident image section directly below historical weather.
- Replaced the two-level gallery heading with a single “Incident Images” heading.
- Moved the image count beside the heading and reduced mobile spacing.

### Deutsch
- Kompakter Einsatzbilder-Bereich direkt unter dem historischen Wetter.
- Zweistufige Galerieüberschrift durch eine einzelne Überschrift „Einsatzbilder“ ersetzt.
- Bildanzahl neben der Überschrift angeordnet und mobile Abstände reduziert.


## 10.3.1 – 2026-08-01

### English
- Completed schema integrations for Yoast SEO, Rank Math, All in One SEO and SEOPress.
- Added centralized schema normalization, live schema inspection and removal of Event nodes.
- Split the incident overview status into Content, Image, Schema and Indexing columns.
- Added publication, noindex, canonical, permalink and image-size checks.
- Made diagnostics and cache notices fully bilingual and improved performance with pagination and caching.
- Cache notices now require explicit confirmation.
- Added compatibility test documentation.

### Deutsch
- Schema-Anbindungen für Yoast SEO, Rank Math, All in One SEO und SEOPress vervollständigt.
- Zentrale Schema-Vereinheitlichung, Live-Schema-Prüfung und Entfernung von Event-Knoten ergänzt.
- Statusübersicht in Inhalt, Bild, Schema und Indexierung aufgeteilt.
- Prüfungen für Veröffentlichung, noindex, Canonical, Permalink und Bildgröße ergänzt.
- Diagnose und Cache-Hinweise vollständig zweisprachig und durch Seitennavigation sowie Cache beschleunigt.
- Cache-Hinweise benötigen nun eine ausdrückliche Bestätigung.
- Kompatibilitätstest-Dokumentation ergänzt.

All important changes to Einsatzlyzer are documented here in English and German.  
Alle wichtigen Änderungen von Einsatzlyzer werden hier auf Englisch und Deutsch dokumentiert.

## 10.3.0 – 2026-08-01 – SEO diagnostics and schema consolidation

### English
- Consolidated structured-data output so an active SEO plugin remains the single schema source.
- Removed Event nodes and nested Event data from Yoast schema for incident reports.
- Added cache detection, a post-update cache notice and a button for supported WordPress caches.
- Added an SEO and image check to the incident editing screen.
- Added image-dimension checks and a clear warning when the preview image is below 1200 × 675 pixels.
- Added a central diagnostics page with plugin, SEO, schema, cache and default-image information.
- Added an SEO status column to the incident overview for all reports.
- Kept user-entered incident reports and media unchanged.

### Deutsch
- Ausgabe strukturierter Daten zentralisiert, sodass ein aktives SEO-Plugin die einzige Schema-Quelle bleibt.
- Event-Knoten und eingebettete Event-Daten aus dem Yoast-Schema für Einsatzberichte entfernt.
- Cache-Erkennung, Hinweis nach Updates und Schaltfläche für unterstützte WordPress-Caches ergänzt.
- SEO- und Bildprüfung auf der Seite „Einsatz bearbeiten“ ergänzt.
- Prüfung der Bildabmessungen mit deutlicher Warnung unter 1200 × 675 Pixeln ergänzt.
- Zentrale Diagnose-Seite mit Plugin-, SEO-, Schema-, Cache- und Standardbildinformationen ergänzt.
- SEO-Statusspalte in „Alle Einsätze“ für sämtliche Berichte ergänzt.
- Selbst eingegebene Einsatzberichte und Medien unverändert gelassen.

## 10.2.4 – 2026-07-31 – Version and documentation consistency update

### English
- Synchronized the plugin header, internal version constant, README, changelog and package names to Version 10.2.4.
- Added the missing release notes for Versions 10.2.2 and 10.2.3.
- Added complete release notes for Version 10.2.4 and updated the README link.
- Removed outdated version numbers from descriptive CSS comments.
- No plugin functions or user-created incident content were changed.

### Deutsch
- Plugin-Header, interne Versionskonstante, README, Changelog und Paketnamen auf Version 10.2.4 vereinheitlicht.
- Fehlende Release Notes für die Versionen 10.2.2 und 10.2.3 ergänzt.
- Vollständige Release Notes für Version 10.2.4 erstellt und README-Link aktualisiert.
- Veraltete Versionsnummern aus beschreibenden CSS-Kommentaren entfernt.
- Plugin-Funktionen und selbst eingegebene Einsatzinhalte wurden nicht verändert.

## 10.2.3 – 2026-07-31 – GitHub presentation and documentation update

### English
- Reworked the GitHub README in the structured bilingual style used by Immich Media Manager.
- Added the large Einsatzlyzer program preview as the first element of the project description.
- Added the supplied German and English screenshots below the main preview.
- Aligned paired screenshots consistently at the top for a cleaner comparison.
- Updated project links, version information and release documentation.
- No user-created incident data or report content was translated or modified.

### Deutsch
- GitHub-README im klar gegliederten zweisprachigen Stil des Immich-Medienmanagers überarbeitet.
- Große Einsatzlyzer-Programmvorschau als erstes Element der Projektbeschreibung eingebunden.
- Gelieferte deutsche und englische Vorschaubilder unterhalb des Hauptbildes ergänzt.
- Paarweise angeordnete Bilder für einen sauberen Vergleich einheitlich oben ausgerichtet.
- Projektlinks, Versionsangaben und Release-Dokumentation aktualisiert.
- Selbst eingegebene Einsatzdaten und Berichtsinhalte wurden weder übersetzt noch verändert.

## 10.2.2 – 2026-07-31 – Extended English administration interface

### English
- Extended the English translation of fixed plugin texts in the incident list.
- Improved English labels and controls on Import / Export and Settings pages.
- Continued the translation review for the incident editing screen.
- Kept WordPress, theme, Yoast SEO and user-entered texts unchanged.
- Improved placement of the light Einsatzlyzer branding in the administration area.

### Deutsch
- Englische Übersetzung fester Plugin-Texte in der Einsatzliste erweitert.
- Englische Beschriftungen und Bedienelemente auf Import / Export und Einstellungen verbessert.
- Übersetzungsprüfung der Seite zum Bearbeiten von Einsätzen fortgesetzt.
- WordPress-, Theme-, Yoast-SEO- und selbst eingegebene Texte unverändert belassen.
- Platzierung des hellen Einsatzlyzer-Brandings im Administrationsbereich verbessert.

## 10.2.1 – 2026-07-30 – English backend translation and branding update

### English
- Expanded the English plugin interface in the WordPress administration area.
- Added further translations to the incident overview and settings pages.
- Integrated the new light Einsatzlyzer logo in suitable administration sections.
- Improved the GitHub screenshot presentation with smaller previews arranged side by side.
- Only fixed Einsatzlyzer interface texts are translated.

### Deutsch
- Englische Plugin-Oberfläche im WordPress-Administrationsbereich erweitert.
- Weitere Übersetzungen in der Einsatzübersicht und auf den Einstellungsseiten ergänzt.
- Neues helles Einsatzlyzer-Logo passend in Verwaltungsbereichen eingebunden.
- GitHub-Bilddarstellung mit kleineren, nebeneinander angeordneten Vorschaubildern verbessert.
- Übersetzt werden ausschließlich feste Oberflächentexte von Einsatzlyzer.

## 10.2.0 – 2026-07-30 – Complete English frontend interface

### English
- Completed the English translation of all fixed texts on individual incident pages.
- Translated headings, fact cards, gallery controls, map labels, sidebar details, sharing controls, related incidents and accessibility labels.
- Localized frontend JavaScript messages for map controls and copied links.
- User-created titles, locations, keywords, reports, vehicle names and unit names remain unchanged.

### Deutsch
- Englische Übersetzung aller festen Texte auf Einsatz-Einzelseiten vervollständigt.
- Überschriften, Infokarten, Galerie, Kartenbeschriftungen, Seitenleiste, Teilen-Funktionen, verwandte Einsätze und Bedienhilfen übersetzt.
- JavaScript-Texte für Kartensteuerung und kopierte Links sprachabhängig gemacht.
- Selbst eingegebene Titel, Orte, Stichwörter, Berichte, Fahrzeug- und Einheitsnamen bleiben unverändert.

## 10.1.1 – 2026-07-30

### English
- Reworked the mobile WordPress incident list to prevent horizontal overflow and overlapping controls.
- Redesigned overview cards so numbers and labels remain readable on narrow screens.
- Separated search, filters and pagination into stable mobile rows.
- Replaced oversized row-action buttons with compact touch-friendly controls.
- Hidden empty and unrelated cache actions from the mobile incident cards.
- Reduced the mobile logo and heading footprint.

### Deutsch
- Mobile WordPress-Einsatzliste neu aufgebaut, damit keine horizontale Verschiebung oder Überlagerung mehr entsteht.
- Übersichtskarten so überarbeitet, dass Zahlen und Bezeichnungen auf schmalen Bildschirmen vollständig lesbar bleiben.
- Suche, Filter und Seitennavigation in stabile eigene Bereiche getrennt.
- Übergroße Aktionsflächen durch kompakte, touchfreundliche Schaltflächen ersetzt.
- Leere und fremde Cache-Aktionen aus den mobilen Einsatzkarten entfernt.
- Logo und Überschrift auf dem Smartphone kompakter dargestellt.

## 10.1.0 – 2026-07-30

### English

- Added comprehensive mobile optimization for the public incident archive.
- Reduced the archive hero height and arranged statistics in a compact two-column grid.
- Improved touch targets, filters, cards, maps and pagination.
- Rebuilt the WordPress incident list as mobile cards with permanently visible actions.
- Improved mobile dashboard overview cards, search and filter controls.

### Deutsch

- Umfassende mobile Optimierung des öffentlichen Einsatzarchivs ergänzt.
- Archiv-Kopfbereich verkleinert und Statistiken kompakt in zwei Spalten angeordnet.
- Touch-Flächen, Filter, Karten, Kartendarstellung und Seitennavigation verbessert.
- WordPress-Einsatzliste als mobile Karten mit dauerhaft sichtbaren Aktionen neu gestaltet.
- Dashboard-Karten, Suche und Filter im mobilen Backend verbessert.

## 10.0.0 – 2026-07-30

### English

- Rebuilt the plugin language system
- Fixed infinite recursion in date formatting
- Completely removed unsafe word-by-word frontend translation
- User-entered incident content, titles, locations and report texts are no longer modified
- Frontend labels are rendered server-side according to the selected plugin language
- Admin translation is limited to complete, known interface texts
- German remains unchanged; English affects Einsatzlyzer only

### Deutsch

- Sprachsystem grundlegend überarbeitet
- Endlosschleife in der Datumsformatierung behoben
- unsichere wortweise Frontend-Übersetzung vollständig entfernt
- eigene Einsatzinhalte, Titel, Orte und Berichtstexte werden nicht mehr verändert
- Frontend-Texte werden serverseitig über die gewählte Plugin-Sprache ausgegeben
- Backend-Übersetzung arbeitet nur noch mit vollständigen, bekannten Oberflächentexten
- Deutsch bleibt unverändert; English betrifft ausschließlich Einsatzlyzer

## 9.8.8 – 2026-07-30

### English

- Converted the complete frontend archive to the selected plugin language
- Added translations for headings, statistics, search, filters, maps, map notices, pagination and incident cards
- Known incident types are shown in English without changing custom terms
- Date and duration formats adapt to English mode
- Improved protection against partial translations in titles, summaries, locations and report texts

### Deutsch

- Frontend-Archiv vollständig auf die gewählte Plugin-Sprache umgestellt
- Übersetzungen für Überschriften, Statistik, Suche, Filter, Karte, Kartenhinweise, Seitennavigation und Einsatzkarten ergänzt
- bekannte Einsatzarten werden auf Englisch angezeigt, ohne benutzerdefinierte Begriffe zu verändern
- Datums- und Dauerangaben werden bei English passend formatiert
- Schutz vor Teilübersetzungen in Titeln, Kurzfassungen, Orten und Berichtstexten erweitert

## 9.8.7 – 2026-07-30

### English

- Prevented partial translation of user-entered incident titles and report content
- Completed English labels for Import / Export and common incident list controls

### Deutsch

- Teilübersetzungen in selbst eingegebenen Einsatzüberschriften und Berichtsinhalten verhindert
- englische Beschriftungen für Import / Export und zentrale Listensteuerungen vervollständigt

## 9.8.6 – 2026-07-30

### English

- Added a central German or English plugin language setting
- Added English output for the Einsatzlyzer backend and frontend
- WordPress, the theme and user-entered incident content remain unchanged
- German output remains identical to previous versions

### Deutsch

- zentrale Plugin-Sprache Deutsch oder English in den Einsatzlyzer-Einstellungen ergänzt
- englische Darstellung des Einsatzlyzer-Backends und Frontends hinzugefügt
- WordPress, Theme und selbst eingegebene Einsatzinhalte bleiben unverändert
- deutsche Darstellung bleibt vollständig wie bisher erhalten

## 9.8.5 – 2026-07-30

### English

- Removed unused image files
- Removed the obsolete `images/markers` folder; current map markers are generated dynamically
- Removed the unused `js/einsatz-frontend.js` file
- Retained the required Leaflet files `marker-icon.png` and `marker-shadow.png`
- Cleaned old release notes and `.gitignore` from the installation package

### Deutsch

- nicht mehr verwendete Bilddateien entfernt
- veralteten Ordner `images/markers` entfernt; aktuelle Kartenmarker werden dynamisch erzeugt
- nicht mehr verwendete Datei `js/einsatz-frontend.js` entfernt
- benötigte Leaflet-Dateien `marker-icon.png` und `marker-shadow.png` beibehalten
- Installationspaket von alten Release Notes und `.gitignore` bereinigt

## 9.8.4 – 2026-07-30

### English

- Added a selectable default image for incidents without a featured or gallery image
- The default image is used for Google NewsArticle, Open Graph and Twitter metadata
- Yoast NewsArticle schema now always includes an image
- Real incident images continue to take priority

### Deutsch

- auswählbares Standardbild für Einsätze ohne Beitrags- oder Galeriebild ergänzt
- Standardbild wird für Google NewsArticle, Open Graph und Twitter verwendet
- Yoast-NewsArticle-Schema enthält nun zuverlässig ein Bild
- vorhandene Einsatzbilder haben weiterhin Vorrang

## 9.8.3 – 2026-07-30

### English

- Corrected the Yoast breadcrumb for incident reports
- Every breadcrumb item now contains a direct URL in `item`
- Names and positions are explicitly set for the homepage, archive and current incident

### Deutsch

- Yoast-Breadcrumb für Einsatzberichte vollständig korrigiert
- jedes Breadcrumb-Element enthält nun eine direkte URL im Feld `item`
- Namen und Positionen für Startseite, Einsatzarchiv und Einsatzbericht eindeutig gesetzt

## 9.8.2 – 2026-07-30

### English

- Removed duplicate breadcrumb output when Yoast SEO is active
- Prevented collisions caused by a shared `#breadcrumb` ID
- Made the standalone breadcrumb output more robust without Yoast

### Deutsch

- doppelte Breadcrumb-Ausgabe bei aktivem Yoast SEO entfernt
- Kollision der gemeinsamen `#breadcrumb`-ID verhindert
- eigenständige Breadcrumb-Ausgabe ohne Yoast robuster aufgebaut

## 9.8.1 – 2026-07-30

### English

- Removed incorrect Event schema from incident reports
- Switched structured data to `NewsArticle`
- Added Yoast schema support for incident reports as NewsArticle
- Incident locations remain available as `contentLocation`
- Google no longer requests event-only fields such as `offers`, `performer`, `organizer` or `endDate`

### Deutsch

- falsche Event-Auszeichnung aus Einsatzberichten entfernt
- strukturierte Daten auf `NewsArticle` umgestellt
- Yoast-Schema für Einsatzberichte als NewsArticle ergänzt
- Einsatzort bleibt als `contentLocation` erhalten
- Google fordert keine Veranstaltungsfelder wie `offers`, `performer`, `organizer` oder `endDate` mehr an

## 9.8.0

### English

- Removed the logo next to “Latest Incident Reports”
- Left the map button as the only action in the archive header
- Rearranged the archive footer with copyright on the left and the transparent Einsatzlyzer logo on the right
- Improved mobile stacking of footer elements
- Kept automatic year output and the author link

### Deutsch

- Logo neben „Aktuelle Einsatzberichte“ vollständig entfernt
- Kartenbutton steht im Archivkopf allein
- Archiv-Footer neu angeordnet: Copyright links, transparentes Einsatzlyzer-Logo rechts
- mobile Darstellung der Footer-Elemente verbessert
- automatische Jahreszahl und Autorenlink beibehalten

## 9.7.9

### English

- Added a new closing section to the incident archive
- Added the transparent Einsatzlyzer logo to the archive footer
- Copyright year updates automatically
- Author name links to ralf-ebert-it.de in a new window

### Deutsch

- neuen Abschlussbereich am Ende der Einsatzübersicht ergänzt
- transparentes Einsatzlyzer-Logo im Archiv-Footer eingebunden
- Copyright-Jahr wechselt automatisch
- Autorenname verlinkt in einem neuen Fenster auf ralf-ebert-it.de

## 9.7.8

### English

- Optimized the archive icon for transparent display
- Removed the mismatching dark logo background
- Matched the logo height to the map button
- Improved responsive presentation

### Deutsch

- Einsatzlyzer-Symbol für die Einsatzübersicht technisch freigestellt
- abweichenden dunklen Logohintergrund entfernt
- Logo auf die Höhe des Kartenbuttons abgestimmt
- responsive Darstellung angepasst

## 9.7.7

### English

- Corrected the Einsatzlyzer admin menu icon across all WordPress admin pages
- Fixed oversized rendering on dashboard, media, pages and other admin screens
- Active and inactive menu states now use the same fixed geometry

### Deutsch

- Einsatzlyzer-Menüsymbol im gesamten Backend korrigiert
- übergroße Darstellung auf Dashboard, Medien, Seiten und anderen Bereichen behoben
- aktive und inaktive Menüzustände verwenden dieselbe feste Geometrie

## 9.7.6

### English

- Removed the logo from the upper public archive area
- Kept the archive heading clean without an image or background box
- Positioned the logo next to “Latest Incident Reports” and before the map button
- Improved the WordPress menu icon
- Reduced the settings page logo further

### Deutsch

- Logo im oberen Bereich des öffentlichen Einsatzarchivs entfernt
- Archivüberschrift ohne Bild und Hintergrundbox belassen
- Logo neben „Aktuelle Einsatzberichte“ und vor dem Kartenbutton platziert
- WordPress-Menüsymbol verbessert
- Logo auf der Einstellungsseite weiter verkleinert

## 9.7.5

### English

- Reduced the logo size on the settings page
- Reliably aligned the archive logo next to “Latest Incident Reports”
- Removed the previously duplicated logo above the heading
- Added a small transparent white WordPress menu icon
- Adjusted sizes and spacing for desktop and mobile

### Deutsch

- Logo auf der Einstellungsseite deutlich verkleinert
- Logo der Einsatzübersicht zuverlässig neben „Aktuelle Einsatzberichte“ angeordnet
- zuvor oberhalb erscheinendes Logo entfernt
- kleines weißes, transparentes WordPress-Menü-Icon erstellt
- Größen und Abstände für Desktop und Mobil angepasst

## 9.7.4

### English

- Added a high-contrast Einsatzlyzer logo for dark backgrounds
- Added the logo to archive, settings, import/export and the WordPress menu
- Arranged admin action links in a compact two-line layout
- Added a clear dark placeholder for incidents without images
- Unified branding throughout the plugin

### Deutsch

- kontrastreiches Einsatzlyzer-Logo für dunkle Hintergründe eingebunden
- Logo in Archiv, Einstellungen, Import/Export und WordPress-Menü ergänzt
- Backend-Aktionslinks kompakt auf zwei Zeilen angeordnet
- dunklen Platzhalter für Einsätze ohne Bild ergänzt
- Branding im gesamten Plugin vereinheitlicht

## 9.7.3

### English

- Enlarged the archive logo to match the Import / Export page
- Brightened the public archive icon for dark backgrounds
- Improved contrast of the WordPress menu icon

### Deutsch

- Logo in der Einsatzübersicht auf die Größe der Import-/Export-Seite vergrößert
- Symbol im öffentlichen Archiv für dunkle Hintergründe aufgehellt
- WordPress-Menü-Icon kontrastreicher gestaltet

## 9.7.2

### English

- Integrated the new Einsatzlyzer branding
- Added compact branding to archive, admin overview, settings and Import / Export
- Added a dedicated WordPress admin menu icon
- Improved responsive presentation of branding elements

### Deutsch

- neues Einsatzlyzer-Logo integriert
- kompaktes Branding in Archiv, Einsatzübersicht, Einstellungen und Import/Export ergänzt
- eigenes Menü-Icon im WordPress-Backend verwendet
- responsive Darstellung der Branding-Elemente optimiert

## 9.7.1

### English

- Improved mobile display of long incident titles
- Reduced heading size on smartphones
- Added a stronger dark gradient for readability
- Optimized spacing and metadata in the mobile image header

### Deutsch

- mobile Darstellung langer Einsatztitel verbessert
- Überschrift auf Smartphones verkleinert
- stärkeren dunklen Verlauf für bessere Lesbarkeit ergänzt
- Innenabstände und Metadaten im mobilen Bild-Kopfbereich optimiert

## 9.7.0

### English

- Completely modernized the backend incident list
- Added compact rows with thumbnails and status indicators
- Added filters by year, incident type and missing information
- Added data overview statistics
- Added extended search by location, keyword and incident number
- Improved display on smaller screens

### Deutsch

- Backend-Einsatzliste vollständig modernisiert
- kompakte Zeilen mit Vorschaubildern und Statusanzeigen ergänzt
- Filter nach Jahr, Einsatzart und fehlenden Angaben hinzugefügt
- Übersichtskennzahlen für den Datenbestand ergänzt
- erweiterte Suche nach Ort, Stichwort und Einsatznummer hinzugefügt
- Darstellung auf kleineren Bildschirmen optimiert

## 9.6.0

### English

- Added complete import and export of incident reports
- Introduced a versioned ZIP format with manifest and checksums
- Included featured images, galleries and embedded report images
- Preserved alt text, captions, descriptions and image credits
- Added stable identifiers for incidents and images
- Added SHA-256 verification
- Added import preview and duplicate detection
- Added skip, update and copy import strategies
- Added resumable step-by-step imports with progress display
- Added optional pre-import backup, rollback, import log and CSV export

### Deutsch

- vollständigen Import und Export von Einsatzberichten ergänzt
- versionsfestes ZIP-Format mit Manifest und Prüfsummen eingeführt
- Beitragsbilder, Galerien und eingebettete Berichtsbilder einbezogen
- Alt-Texte, Bildunterschriften, Beschreibungen und Bildquellen übernommen
- stabile Kennungen für Einsätze und Bilder ergänzt
- SHA-256-Prüfung hinzugefügt
- Importvorschau und Duplikaterkennung ergänzt
- Strategien zum Überspringen, Aktualisieren und Kopieren hinzugefügt
- fortsetzbaren Import mit Fortschrittsanzeige ergänzt
- Vorab-Backup, Rücksetzung, Importprotokoll und CSV-Export hinzugefügt

## 9.5.2

### English

- Completely removed the experimental incident area polygon
- Removed boundary, area, legend and related settings
- Kept markers, clusters, filters and individual maps
- Kept street, topographic and high-contrast map styles

### Deutsch

- experimentelle Einsatzgebietsfläche vollständig entfernt
- Grenzlinie, Fläche, Legende und zugehörige Einstellungen bereinigt
- Einsatzmarker, Cluster, Filter und Einzelkarten beibehalten
- Straßenkarte, topografische und kontrastreiche Kartenansicht beibehalten

## 9.5.1

### English

- Improved social media previews for WhatsApp, Facebook and X
- Reliably uses the featured image or first gallery image
- Added support for Yoast SEO, Rank Math and SEOPress
- Added a neutral fallback image for reports without an incident image

### Deutsch

- Social-Media-Vorschaubilder für WhatsApp, Facebook und X verbessert
- Beitragsbild oder erstes Galeriebild wird zuverlässig verwendet
- Unterstützung für Yoast SEO, Rank Math und SEOPress ergänzt
- neutrales Ersatzbild bei Berichten ohne Einsatzbild ergänzt

## 9.4.8

### English

- Tested an extended incident area display on the overview map
- Added configurable boundary, fill, line width, opacity and legend
- The feature was later removed in version 9.5.2

### Deutsch

- erweiterte Darstellung eines Einsatzgebiets auf der Gesamtkarte erprobt
- Grenze, Fläche, Linienstärke, Deckkraft und Legende ergänzt
- Funktion später in Version 9.5.2 vollständig entfernt

## 9.4.6

### English

- Unified incident cards on desktop and tablet
- Standardized title area height
- Aligned time, location and report link at the bottom of each card
- Kept mobile card heights content-based to avoid empty space

### Deutsch

- Einsatzkarten im Archiv auf Desktop und Tablet vereinheitlicht
- einheitliche Höhe der Titelbereiche ergänzt
- Zeit, Ort und Bericht-Link am unteren Kartenrand ausgerichtet
- mobile Karten bleiben inhaltsabhängig und vermeiden Leerflächen

## 9.4.5

### English

- Pagination and live filters now load the public incident page
- Removed dependency on `wp-admin/admin-ajax.php`
- Fixed issues with additionally protected WordPress admin areas
- Added a safe fallback to the public page on loading errors
- Enlarged and clarified the map button

### Deutsch

- Seitennavigation und Live-Filter laden nun die öffentliche Einsatzseite
- Zugriff über `wp-admin/admin-ajax.php` entfernt
- Probleme mit geschützten WordPress-Adminbereichen behoben
- sicheren Rückfall auf die öffentliche Seite ergänzt
- Kartenbutton vergrößert und verständlicher beschriftet

## 9.4.4

### English

- Back links now lead to the selected public incident archive page
- Technical archives redirect to the designed archive page
- Prevented duplicate archive pages
- Added a selectable saved Elementor footer template for incident reports
- Elementor Pro is not required

### Deutsch

- Zurück-Link führt auf die ausgewählte öffentliche Einsatzübersichtsseite
- technisches Archiv wird auf die gestaltete Übersichtsseite weitergeleitet
- doppelte Übersichtsseiten werden vermieden
- gespeicherte Elementor-Footervorlage für Einsatzberichte ergänzt
- Elementor Pro ist nicht erforderlich

## 9.4.3

### English

- Removed large white gaps below manually embedded Elementor menu templates
- Prevented duplicate Elementor roots using the same template ID
- Cleaned up sticky placeholders and oversized containers on detail pages

### Deutsch

- großen weißen Leerraum unter Elementor-Menüvorlagen entfernt
- doppelte Elementor-Wurzel mit identischer Vorlagen-ID verhindert
- Sticky-Platzhalter und übergroße Container auf Einzelseiten bereinigt

## 9.4.2

### English

- Added selectable saved Elementor menu templates for incident detail pages
- Works with the free Elementor version
- Existing pages and posts remain unchanged

### Deutsch

- gespeicherte Elementor-Menüvorlage für Einsatz-Einzelseiten auswählbar gemacht
- Verwendung mit der kostenlosen Elementor-Version ermöglicht
- bestehende Seiten und Beiträge bleiben unberührt

## 9.4.1

### English

- Fully switched active map functionality to OpenStreetMap and Leaflet
- Removed Google Maps and HERE from active use
- No API keys or billing account required
- Added optional straight-line distance
- Added external route planning through OpenStreetMap
- Added exact, approximate or hidden incident positions per report

### Deutsch

- aktive Kartenfunktion vollständig auf OpenStreetMap und Leaflet umgestellt
- Google Maps und HERE aus dem aktiven Umfang entfernt
- keine API-Schlüssel und kein Abrechnungskonto erforderlich
- optionale Luftlinienentfernung ergänzt
- externe Routenplanung über OpenStreetMap hinzugefügt
- genaue, angenäherte oder ausgeblendete Position je Bericht ermöglicht

## 9.3.0

### English

- Handed all header output to the active WordPress theme
- Removed additional header output from Einsatzlyzer
- Improved compatibility with Elementor, Jeg Kit and Theme Builders
- Existing Theme Builder conditions remain authoritative

### Deutsch

- Header-Ausgabe vollständig an das aktive WordPress-Theme übergeben
- zusätzliche Header-Ausgabe durch Einsatzlyzer entfernt
- Zusammenarbeit mit Elementor, Jeg Kit und Theme Buildern verbessert
- bestehende Theme-Builder-Bedingungen bleiben maßgeblich

## 9.2.3

### English

- Further optimized desktop featured image display
- Added a fixed and reliable image header height
- Improved image positioning and spacing
- Stabilized the header fallback

### Deutsch

- Desktop-Darstellung des Titelbildes weiter optimiert
- feste und zuverlässige Höhe des Bild-Kopfbereichs ergänzt
- Bildpositionierung und Abstände verbessert
- Header-Fallback stabilisiert

## 9.2.2

### English

- Made the incident detail header more compact
- Improved featured image height on desktop
- Improved reliability of Elementor header integration

### Deutsch

- Kopfbereich einzelner Einsatzberichte kompakter gestaltet
- Titelbildhöhe auf Desktop-Geräten verbessert
- Elementor-Header zuverlässiger eingebunden

## 9.2.0

### English

- Added map provider selection and optional Google Maps integration
- Added consent before loading external Google maps

### Deutsch

- Kartenanbieter-Auswahl und optionale Google-Maps-Einbindung ergänzt
- Einwilligung vor dem Laden externer Google-Karten hinzugefügt
