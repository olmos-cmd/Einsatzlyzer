# Änderungsverlauf

Alle wichtigen Änderungen von Einsatzlyzer werden in dieser Datei dokumentiert.

## 9.7.0

- Backend-Einsatzliste vollständig modernisiert
- kompakte, einheitliche Zeilen mit Vorschaubildern
- Statusanzeigen für Galerie, Titelbild, Koordinaten und Vollständigkeit
- Filter nach Jahr, Einsatzart und fehlenden Angaben
- Übersichtskennzahlen für den Datenbestand
- erweiterte Suche nach Ort, Stichwort und Einsatznummer
- optimierte Darstellung auf kleineren Bildschirmen

## 9.6.0

- vollständiger Import und Export von Einsatzberichten
- eigenes versionsfestes ZIP-Format mit Manifest und Prüfsummen
- Beitragsbilder, Galerien und eingebettete Berichtsbilder werden mitgesichert
- Alt-Texte, Bildunterschriften, Beschreibungen und Bildquellen werden übernommen
- stabile Kennungen für Einsätze und Bilder
- SHA-256-Prüfung gegen beschädigte oder doppelte Dateien
- Importvorschau und Duplikaterkennung
- Strategien: überspringen, aktualisieren oder als Kopie importieren
- Import in Teilschritten mit Fortschrittsanzeige und Fortsetzung
- optionales Vorab-Backup und Rücksetzung einer Import-Sitzung
- Importprotokoll und zusätzlicher CSV-Export

## 9.5.2

- experimentelle Einsatzgebietsfläche vollständig entfernt
- Grenzlinie, Fläche, Legende und zugehörige Einstellungen bereinigt
- Einsatzmarker, Cluster, Filter und Einzelkarten bleiben erhalten
- Straßenkarte, topografische und kontrastreiche Kartenansicht bleiben verfügbar

## 9.5.1

- Social-Media-Vorschaubilder für WhatsApp, Facebook und X verbessert
- Beitragsbild oder erstes Galeriebild wird zuverlässig verwendet
- Unterstützung für Yoast SEO, Rank Math und SEOPress ergänzt
- neutrales Ersatzbild bei Berichten ohne Einsatzbild

## 9.4.8

- erweiterte Darstellung eines Einsatzgebiets auf der Gesamtkarte erprobt
- Grenze, Fläche, Linienstärke und Deckkraft einstellbar
- Kartenlegende ergänzt
- Funktion später in Version 9.5.2 wieder vollständig entfernt

## 9.4.6

- Einsatzkarten im Archiv auf Desktop und Tablet vereinheitlicht
- einheitliche Höhe der Titelbereiche
- Zeit, Ort und Bericht-Link am unteren Kartenrand ausgerichtet
- mobile Karten bleiben inhaltsabhängig und vermeiden unnötige Leerflächen

## 9.4.5

- Seitennavigation und Live-Filter laden die öffentliche Einsatzseite
- kein Zugriff über `wp-admin/admin-ajax.php` mehr erforderlich
- Probleme mit zusätzlich geschützten WordPress-Adminbereichen behoben
- sicherer Rückfall auf die öffentliche Seite bei Ladefehlern
- Kartenbutton vergrößert und verständlicher beschriftet

## 9.4.4

- Zurück-Link führt auf die ausgewählte öffentliche Einsatzübersichtsseite
- technisches Archiv wird auf die gestaltete Übersichtsseite weitergeleitet
- doppelte Übersichtsseiten werden vermieden
- gespeicherte Elementor-Footervorlage für Einsatzberichte ergänzt
- Elementor Pro ist dafür nicht erforderlich

## 9.4.3

- großer weißer Leerraum unter manuell eingebundenen Elementor-Menüvorlagen entfernt
- doppelte Elementor-Wurzel mit identischer Vorlagen-ID vermieden
- Sticky-Platzhalter und übergroße Container auf Einsatz-Einzelseiten bereinigt

## 9.4.2

- gespeicherte Elementor-Menüvorlage für Einsatz-Einzelseiten auswählbar
- Verwendung auch mit der kostenlosen Elementor-Version möglich
- normale Seiten und Beiträge bleiben unberührt

## 9.4.1

- vollständige Umstellung auf OpenStreetMap und Leaflet
- Google Maps und HERE aus dem aktiven Funktionsumfang entfernt
- keine API-Schlüssel und kein Abrechnungskonto mehr erforderlich
- optionale Entfernung als Luftlinie
- externe Routenplanung über OpenStreetMap
- genaue, angenäherte oder ausgeblendete Einsatzposition je Bericht

## 9.3.0

- Header-Ausgabe vollständig an das aktive WordPress-Theme übergeben
- keine zusätzliche Header-Ausgabe durch Einsatzlyzer
- bessere Zusammenarbeit mit Elementor, Jeg Kit und Theme Buildern
- bestehende Bedingungen des Theme Builders bleiben maßgeblich

## 9.2.3

- Desktop-Darstellung des Titelbildes weiter optimiert
- feste und zuverlässige Höhe des Bild-Kopfbereichs
- Bildpositionierung und Abstände verbessert
- Header-Fallback stabilisiert

## 9.2.2

- Kopfbereich einzelner Einsatzberichte kompakter gestaltet
- Titelbildhöhe auf Desktop-Geräten verbessert
- Elementor-Header zuverlässiger eingebunden

## 9.2.0

- Kartenanbieter-Auswahl und optionale Google-Maps-Einbindung ergänzt
- Einwilligung vor dem Laden externer Google-Karten
- automatischer Rückfall auf OpenStreetMap bei fehlender Konfiguration
- Elementor-Kompatibilität und Kartenfehlermeldungen verbessert
- diese Anbieter-Auswahl wurde später in Version 9.4.1 zugunsten von OpenStreetMap entfernt

## 9.1.0

- Einsatzarchiv umfassend modernisiert
- moderner Kopfbereich mit Statistik
- Live-Suche nach Stichwort, Ort und Bericht
- Filter nach Jahr und Einsatzart
- aktive Filter einzeln entfernbar
- responsive Einsatzkarten und automatische Vorschaubilder
- einblendbare Gesamtkarte mit Marker-Clustern
- Seitennavigation für ältere Berichte
- moderne Einzelseite mit Galerie, Karte und verwandten Einsätzen
- Teilen-Funktionen und verbesserte Tastaturbedienung
- Canonical-URL, Meta-Daten, Social-Media-Vorschau und strukturierte Daten

---

# Frühe Entwicklung von Einsatzlyzer

Die folgenden Einträge dokumentieren die frühen Entwicklungsstände von Einsatzlyzer vor dem umfassenden Neuaufbau der 9.x-Reihe. Doppelt verwendete interne Versionsnummern wurden zu einem gemeinsamen Eintrag zusammengeführt.

## 1.9.4

- Beginn und Ende des Einsatzes im Frontend
- automatische Berechnung der Einsatzdauer
- Einsätze über Mitternacht werden berücksichtigt

## 1.9.3

- Filter nach Einsatzkategorie ergänzt
- Kategorie wird gemeinsam mit Jahr und Monat berücksichtigt
- Backend-Menü später in „FFW Einsatzberichte“ umbenannt
- unnötige Archiv- und Kartenüberschriften entfernt

## 1.9.2

- strukturierte Eingabe von Datum, Beginn und Ende
- manuelle Einsatznummer
- Einsatzdetails im Frontend ein- oder ausblendbar
- Jümme-Polygon vollständig in das Plugin übernommen

## 1.9.1

- robustere Verarbeitung der Kartendaten
- Fehlerbehandlung für ungültige Koordinaten verbessert
- Probleme und kritische Fehler in der Archivkarte korrigiert

## 1.9

- Einsatznummerierung nach Jahren
- Karte direkt neben der Einsatzliste
- Schaltfläche „Auf Karte anzeigen“
- gefilterte Einsätze werden auf der Archivkarte dargestellt

## 1.8

- Standardboxen für Beitragsbild und Kategorien ausgeblendet
- eigene Felder für Vorschaubild und Einsatzkategorie
- Archiv- und Einzelansicht auf das neue Vorschaubildfeld umgestellt

## 1.7

- eigene Leaflet-Karte direkt im WordPress-Backend
- Einsatzort per Mausklick auswählbar
- Koordinaten werden direkt als Einsatzdaten gespeichert
- kein zusätzliches ACF-Karten-Plugin mehr zwingend erforderlich

## 1.6

- korrigierte Position des Feuerwehrhauses
- eigenes PNG-Symbol für das Feuerwehrhaus
- Feuerwehrhaus und Jümme-Umrandung auf allen Karten
- bisherige Karten-, Filter- und Layoutfunktionen zusammengeführt

## 1.5

- Filter nach Jahr und Monat
- Filterung auch auf der Übersichtskarte
- Einsatzdetails und Karte in der Einzelansicht nebeneinander
- responsive Darstellung für Tablets und Smartphones

## 1.4

- WordPress-Standardeditor für Einsätze entfernt
- übersichtlichere Eingabe über eigene Felder
- Berichtstext über einen eigenen WYSIWYG-Editor
- Karten- und Einsatzdaten strukturierter erfasst

## 1.3

- Übersichtskarte für alle Einsätze ergänzt
- neuer Shortcode für die Kartenansicht
- Einsatzmarker mit Verlinkung zum Bericht
- Marker-Gruppierung für viele Einsätze vorbereitet

## 1.2

- Inhaltstyp von `einsatz` auf `einsatzbericht` umbenannt
- Konflikt mit einer bereits vorhandenen Einsatzverwaltung behoben
- URLs und Templates auf „Einsatzberichte“ angepasst

## 1.1

- erste funktionsfähige Version der Einsatzverwaltung
- eigener Inhaltstyp für Einsätze
- Archiv- und Einzelansicht
- Einsatzdaten, Fahrzeuge und Bildergalerie
- OpenStreetMap-/Leaflet-Karte
- Einsatzgebiet Jümme und Feuerwehrhaus auf der Karte
