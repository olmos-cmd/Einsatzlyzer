<?php
/**
 * Plugin Name:       Einsatzlyzer
 * Description:       Moderne Einsatzverwaltung mit responsivem Einsatzarchiv, Einzelseiten, Galerie, Karten und SEO-freundlicher Ausgabe.
 * Version:           10.2.2
 * Author:            Ralf Ebert
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       einsatzlyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'FFL_EINSATZLYZER_VERSION', '10.2.2' );
define( 'FFL_EINSATZLYZER_FILE', __FILE__ );
define( 'FFL_EINSATZLYZER_DIR', plugin_dir_path( __FILE__ ) );
define( 'FFL_EINSATZLYZER_URL', plugin_dir_url( __FILE__ ) );

require_once FFL_EINSATZLYZER_DIR . 'includes/import-export.php';

/**
 * Plugin-eigene Sprache. Sie ändert ausschließlich Einsatzlyzer und lässt
 * WordPress, Theme sowie die vom Benutzer erfassten Einsatzinhalte unberührt.
 */
function ffl_get_plugin_language() {
    return 'en' === get_option( 'ffl_plugin_language', 'de' ) ? 'en' : 'de';
}

function ffl_lang( $de, $en ) {
    return 'en' === ffl_get_plugin_language() ? $en : $de;
}


/** Translate only known plugin taxonomy labels; never touch user-entered content. */
function ffl_term_display_name( $name ) {
    $known = array(
        'Brandeinsatz' => 'Fire Incident',
        'Technische Hilfeleistung' => 'Technical Assistance',
        'Fehlalarm' => 'False Alarm',
        'Übungseinsatz' => 'Training Exercise',
    );
    return ( 'en' === ffl_get_plugin_language() && isset( $known[ $name ] ) ) ? $known[ $name ] : $name;
}

/** Format archive dates independently from the WordPress site language. */
function ffl_archive_date( $timestamp ) {
    if ( 'en' !== ffl_get_plugin_language() ) {
        return wp_date( 'd. F Y', $timestamp );
    }
    $months = array( 1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December' );
    return wp_date( 'd', $timestamp ) . ' ' . $months[ (int) wp_date( 'n', $timestamp ) ] . ' ' . wp_date( 'Y', $timestamp );
}

function ffl_sanitize_plugin_language( $value ) {
    return 'en' === sanitize_key( (string) $value ) ? 'en' : 'de';
}

function ffl_language_dictionary() {
    return array(
        'Einsätze' => 'Incidents', 'Einsatz' => 'Incident', 'Alle Einsätze' => 'All Incidents',
        'Neu erstellen' => 'Add New', 'Neuen Einsatz erstellen' => 'Add New Incident',
        'Einsatz bearbeiten' => 'Edit Incident', 'Neuer Einsatz' => 'New Incident',
        'Einsatz ansehen' => 'View Incident', 'Einsätze ansehen' => 'View Incidents',
        'Einsätze durchsuchen' => 'Search Incidents', 'Keine Einsätze gefunden' => 'No incidents found',
        'Keine Einsätze im Papierkorb gefunden' => 'No incidents found in Trash',
        'Einsatzbild' => 'Incident Image', 'Einsatzbild festlegen' => 'Set Incident Image',
        'Einsatzarchive' => 'Incident Archives', 'Einsatz-Eigenschaften' => 'Incident Attributes',
        'Einsatzarten' => 'Incident Types', 'Einsatzart' => 'Incident Type',
        'Einstellungen' => 'Settings', 'Einsatzlyzer Einstellungen' => 'Einsatzlyzer Settings',
        'Darstellung, Navigation und Karten zentral verwalten.' => 'Manage display, navigation and maps centrally.',
        'Name der Feuerwehr' => 'Fire Department Name', 'Einleitung Einsatzarchiv' => 'Incident Archive Introduction',
        'Seite der Einsatzübersicht' => 'Incident Overview Page', 'Startnummer aktuelles Jahr' => 'Starting Number for Current Year',
        'Menü auf Einsatz-Einzelseiten' => 'Menu on Individual Incident Pages',
        'Elementor-Menüvorlage' => 'Elementor Menu Template', 'Footer auf Einsatz-Einzelseiten' => 'Footer on Individual Incident Pages',
        'Elementor-Footervorlage' => 'Elementor Footer Template', 'Titelbild auf Einsatz-Einzelseiten' => 'Hero Image on Individual Incident Pages',
        'Kartenanbieter' => 'Map Provider', 'Entfernung zum Einsatzort' => 'Distance to Incident Location',
        'Ausgangspunkt Feuerwehrhaus' => 'Starting Point Fire Station', 'Änderungen speichern' => 'Save Changes',
        'Plugin-Sprache' => 'Plugin Language', 'Deutsch' => 'German', 'Englisch' => 'English',
        'Einsatzarchiv' => 'Incident Archive', 'Aktuelle Einsatzberichte' => 'Latest Incident Reports',
        'Ergebnisse' => 'Results', 'Ergebnis' => 'Result', 'Alle Jahre' => 'All Years', 'Jahr' => 'Year',
        'Alle Einsatzarten' => 'All Incident Types', 'Suchen' => 'Search', 'Suche' => 'Search',
        'Filter zurücksetzen' => 'Reset Filters', 'Einsatzorte auf Karte anzeigen' => 'Show Incident Locations on Map',
        'Karte wieder schließen' => 'Close Map', 'Zurück zu den Einsatzberichten' => 'Back to Incident Reports',
        'Einsatzbericht ansehen' => 'View Incident Report', 'Keine Einsätze gefunden' => 'No Incidents Found',
        'Für diese Auswahl sind keine Einsatzberichte vorhanden.' => 'No incident reports are available for this selection.',
        'Alle Einsätze anzeigen' => 'Show All Incidents', 'Alle Einsätze' => 'All Incidents',
        'Weitere Einsätze' => 'More Incidents', 'Das könnte ebenfalls interessieren' => 'You May Also Be Interested In',
        'Zum Einsatzarchiv' => 'Go to Incident Archive', 'Interaktive Karte des Einsatzortes' => 'Interactive Map of the Incident Location',
        'Einsatzdetails' => 'Incident Details', 'Alarmierung' => 'Alert', 'Einsatzort' => 'Incident Location',
        'Alarmstichwort' => 'Alert Keyword', 'Einsatznummer' => 'Incident Number', 'Fahrzeuge' => 'Vehicles',
        'Bericht' => 'Report', 'Bilder' => 'Images', 'Galerie' => 'Gallery', 'Teilen' => 'Share',
        'Link kopiert' => 'Link copied', 'Einsätze werden geladen …' => 'Incidents are loading …',
        'Die Karte konnte nicht geladen werden.' => 'The map could not be loaded.',
        'Import / Export' => 'Import / Export', 'Alle Einsatzberichte' => 'All Incident Reports',
        'Zurück' => 'Back', 'Route in OpenStreetMap planen' => 'Plan Route in OpenStreetMap',
        'Breitengrad' => 'Latitude', 'Längengrad' => 'Longitude', 'Nicht anzeigen' => 'Do not display',
        'Luftlinie vom Feuerwehrhaus' => 'Straight-line distance from fire station',
        'Keine Vorlage ausgewählt' => 'No template selected', 'Gespeicherte Elementor-Vorlagen öffnen' => 'Open Saved Elementor Templates',
        'Kompakt – ca. 350 px (empfohlen)' => 'Compact – approx. 350 px (recommended)',
        'Normal – ca. 430 px' => 'Standard – approx. 430 px', 'Groß – ca. 520 px' => 'Large – approx. 520 px',
        'Standardbild für Einsätze ohne Bild' => 'Default Image for Incidents Without an Image',
        'Bild auswählen' => 'Select Image', 'Bild entfernen' => 'Remove Image',
        'Fehlerhafte Einsatz-Links reparieren' => 'Repair Invalid Incident Links',
        'Einsatz-Links jetzt prüfen' => 'Check Incident Links Now',

        'Einsätze sichern, übertragen und wiederherstellen' => 'Back Up, Transfer and Restore Incidents',
        'Import und Export' => 'Import and Export',
        'Die vollständige ZIP-Sicherung enthält Einsatzdaten, Beitragsbilder, Galerien, eingebettete Bilder, Bildtexte und Prüfsummen. CSV ist zusätzlich für Excel-Auswertungen gedacht.' => 'The complete ZIP backup contains incident data, featured images, galleries, embedded images, image text and checksums. CSV is additionally intended for Excel analysis.',
        'Vollständige Sicherung' => 'Complete Backup',
        'Erzeugt eine eigenständige ZIP-Datei mit JSON-Daten und den echten Originalbildern.' => 'Creates a self-contained ZIP file with JSON data and the original images.',
        'Bilder einschließen' => 'Include Images',
        'Beitragsbilder' => 'Featured Images',
        'Galeriebilder' => 'Gallery Images',
        'Im Bericht eingebettete Bilder' => 'Images Embedded in the Report',
        'Vollständige Sicherung erstellen' => 'Create Complete Backup',
        'CSV für Excel' => 'CSV for Excel',
        'Semikolon-getrennt, UTF-8 und mit allen Einsatzfeldern sowie Bild-URLs.' => 'Semicolon-separated, UTF-8 and including all incident fields and image URLs.',
        'CSV herunterladen' => 'Download CSV',
        'Einsatzlyzer-Sicherung importieren' => 'Import Einsatzlyzer Backup',
        'Das Archiv wird zuerst vollständig geprüft. Es werden noch keine Inhalte verändert.' => 'The archive is fully checked first. No content is changed at this stage.',
        'ZIP-Sicherung auswählen' => 'Select ZIP Backup',
        'Nur ZIP-Dateien, die mit Einsatzlyzer erstellt wurden.' => 'Only ZIP files created with Einsatzlyzer.',
        'Archiv prüfen und Vorschau öffnen' => 'Check Archive and Open Preview',
        'Sicherheitsprinzip' => 'Safety Principle',
        'Importe laufen in kleinen Paketen. Doppelte Einsätze werden vorab erkannt, Bilder anhand ihrer SHA-256-Prüfsumme wiederverwendet und Änderungen können innerhalb der Sitzung zurückgesetzt werden.' => 'Imports run in small batches. Duplicate incidents are detected in advance, images are reused based on their SHA-256 checksum, and changes can be rolled back within the session.',
        'Auswahl' => 'Selection', 'Zeitraum' => 'Date Range', 'Von' => 'From', 'Bis' => 'To', 'bis' => 'to',
        'Status' => 'Status', 'Veröffentlicht' => 'Published', 'Entwürfe' => 'Drafts', 'Ausstehend' => 'Pending', 'Privat' => 'Private', 'Geplant' => 'Scheduled',
        'Import-Sitzung' => 'Import Session', 'Importfortschritt' => 'Import Progress', 'Nächstes Paket importieren' => 'Import Next Batch',
        'Import anhalten' => 'Pause Import', 'Änderungen dieser Sitzung zurücksetzen' => 'Roll Back Changes from This Session',
        'Vorab-Backup herunterladen' => 'Download Pre-Import Backup', 'Importprotokoll herunterladen' => 'Download Import Log',
        'Sitzung abschließen und temporäre Dateien löschen' => 'Finish Session and Delete Temporary Files',
        'Vorschau' => 'Preview', 'Import läuft' => 'Import Running', 'Abgeschlossen' => 'Completed', 'Fehler' => 'Error', 'Angehalten' => 'Paused', 'Zurückgesetzt' => 'Rolled Back',
        'Einsätze gefunden' => 'Incidents Found', 'voraussichtlich neu' => 'Expected New', 'bereits vorhanden' => 'Already Present', 'Bilder im Archiv' => 'Images in Archive',
        'Quelle:' => 'Source:', 'Unbekannte Website' => 'Unknown Website', 'ohne Datum' => 'No Date', 'Erkennung' => 'Detection', 'Vorhanden:' => 'Existing:', 'Neu' => 'New',
        'Vorhandene Einsätze' => 'Existing Incidents', 'Überspringen' => 'Skip', 'Sicherste Einstellung; vorhandene Inhalte bleiben unverändert.' => 'Safest setting; existing content remains unchanged.',
        'Aktualisieren' => 'Update', 'Daten, Taxonomie und Bilder aus der Sicherung übernehmen.' => 'Apply data, taxonomy and images from the backup.',
        'Als Kopie importieren' => 'Import as Copy', 'Erstellt bei Treffern einen neuen Entwurf.' => 'Creates a new draft when a match is found.',
        'Status nach Import' => 'Status After Import', 'Status aus Sicherung beibehalten' => 'Keep Status from Backup', 'Alles als Entwurf' => 'Import Everything as Draft', 'Alles veröffentlichen' => 'Publish Everything',
        'Optionen' => 'Options', 'Bilder importieren und zuordnen' => 'Import and Assign Images', 'Vorher vollständiges Backup erstellen' => 'Create a Complete Backup First',
        'Bestehende Links bei Aktualisierungen beibehalten' => 'Keep Existing Links When Updating', 'Import starten' => 'Start Import', 'Abbrechen und Dateien löschen' => 'Cancel and Delete Files',
        'neu' => 'new', 'aktualisiert' => 'updated', 'Kopien' => 'copies', 'übersprungen' => 'skipped', 'Bilder neu' => 'new images', 'Bilder wiederverwendet' => 'images reused',
        'Mehrfachaktionen' => 'Bulk actions', 'Anwenden' => 'Apply', 'Alle Vollständigkeiten' => 'All completeness levels', 'Alle SEO-Werte' => 'All SEO scores',
        'Bild' => 'Image', 'Bearbeiten' => 'Edit', 'Schnellbearbeitung' => 'Quick Edit', 'Papierkorb' => 'Trash', 'Anzeigen' => 'View', 'Kopieren' => 'Copy', 'Duplizieren' => 'Duplicate',
        'Vollständig exportieren' => 'Export Complete', 'Purge from cache' => 'Purge from cache',
        'Ändert ausschließlich Einsatzlyzer im Backend und Frontend. Deine eingegebenen Einsatzberichte bleiben unverändert.' => 'Changes only Einsatzlyzer in the backend and frontend. Your entered incident reports remain unchanged.',
        'Noch kein eigenes Standardbild ausgewählt' => 'No custom default image selected yet',
        'Ausgewähltes Standardbild' => 'Selected default image',
        'Auswahl entfernen' => 'Remove Selection',
        'Dieses Bild wird für Google, soziale Netzwerke und strukturierte Artikeldaten verwendet, wenn ein Einsatz weder Beitragsbild noch Galeriebild besitzt. Das Bild sollte mindestens 1200 × 675 Pixel groß sein.' => 'This image is used for Google, social networks and structured article data when an incident has neither a featured image nor a gallery image. The image should be at least 1200 × 675 pixels.',
        'Automatisch erkennen (empfohlen)' => 'Detect Automatically (recommended)',
        'Wähle die Elementor-Seite mit dem Shortcode [ffl_einsatz_liste_komplett]. „Alle Einsätze“, „Zum Einsatzarchiv“ und das technische Archiv führen dann auf diese Seite.' => 'Select the Elementor page containing the shortcode [ffl_einsatz_liste_komplett]. “All Incidents”, “Go to Incident Archive” and the technical archive will then link to this page.',
        'Wird nur verwendet, wenn keine manuelle Einsatznummer hinterlegt ist.' => 'Used only when no manual incident number has been entered.',
        'Normalen Theme-Header verwenden' => 'Use the Normal Theme Header',
        'Gespeicherte Elementor-Vorlage oberhalb des Einsatzbildes anzeigen' => 'Display a Saved Elementor Template Above the Incident Image',
        'Die zweite Auswahl funktioniert auch mit der kostenlosen Elementor-Version. Sie gilt ausschließlich für einzelne Einsatzberichte; normale Seiten und Beiträge bleiben unverändert.' => 'The second option also works with the free Elementor version. It applies only to individual incident reports; normal pages and posts remain unchanged.',
        'Noch keine gespeicherte Elementor-Vorlage vorhanden.' => 'No Saved Elementor Template Available Yet.',
        'Öffne die Seite „Einsätze“ mit Elementor, klicke den äußersten Container deines originalen Menüs mit der rechten Maustaste an und wähle „Als Vorlage speichern“. Danach erscheint sie hier.' => 'Open the “Incidents” page with Elementor, right-click the outermost container of your original menu and select “Save as Template”. It will then appear here.',
        'Wähle hier die Vorlage mit deinem Feuerwehrlogo und dem originalen Menü. Keine automatische Erkennung und keine Pro-Version erforderlich.' => 'Select the template containing your fire department logo and original menu. No automatic detection or Pro version is required.',
        'Nur normalen Theme-Footer verwenden' => 'Use Only the Normal Theme Footer',
        'Gespeicherte Elementor-Vorlage am Seitenende anzeigen' => 'Display a Saved Elementor Template at the End of the Page',
        'Die Vorlage wird nur bei einzelnen Einsatzberichten unmittelbar vor dem normalen Theme-Footer ausgegeben.' => 'The template is displayed only on individual incident reports, directly before the normal theme footer.',
        'Speichere den äußeren Container deines bestehenden Footers in Elementor als Vorlage und wähle ihn hier aus. Elementor Pro ist nicht erforderlich.' => 'Save the outer container of your existing footer as an Elementor template and select it here. Elementor Pro is not required.',
        'Gilt nur auf PC und größeren Tablets. Auf dem Handy bleibt die bewährte mobile Darstellung erhalten.' => 'Applies only to desktop computers and larger tablets. The established mobile layout remains unchanged on phones.',
        'Einsatzlyzer verwendet ausschließlich OpenStreetMap. Es wird kein Google- oder HERE-API-Schlüssel benötigt und es muss kein Abrechnungskonto hinterlegt werden.' => 'Einsatzlyzer uses OpenStreetMap exclusively. No Google or HERE API key or billing account is required.',
        'Die Entfernung wird ausschließlich aus den gespeicherten Koordinaten des Feuerwehrhauses und des Einsatzortes berechnet. Der Standort des Besuchers wird nicht abgefragt oder gespeichert.' => 'The distance is calculated exclusively from the stored coordinates of the fire station and incident location. The visitor’s location is neither requested nor stored.',
        'Diese Koordinaten sind der feste Startpunkt für die Luftlinienentfernung und den Button „Route in OpenStreetMap planen“.' => 'These coordinates are the fixed starting point for the straight-line distance and the “Plan Route in OpenStreetMap” button.',
        'Linkprüfung abgeschlossen:' => 'Link Check Completed:',
        'fehlerhafte Einsatz-Links wurden korrigiert. Bitte deine Sitemap anschließend neu erzeugen.' => 'invalid incident links were corrected. Please regenerate your sitemap afterwards.',
        'Prüft alte Einsatz-URLs wie /14207/, /page/ oder /seite-2/ und ersetzt nur diese eindeutig fehlerhaften Slugs durch sprechende Links aus dem Einsatz-Titel. Gute bestehende URLs bleiben unverändert. Alte Adressen werden als WordPress-Weiterleitung gespeichert.' => 'Checks old incident URLs such as /14207/, /page/ or /seite-2/ and replaces only these clearly invalid slugs with readable links based on the incident title. Valid existing URLs remain unchanged. Old addresses are stored as WordPress redirects.',
        'Sitemap:' => 'Sitemap:',
        'Einsatzlyzer erzeugt bewusst keine eigene Sitemap. Alle veröffentlichten Einsätze sind öffentliche, indexierbare Einzelseiten und können von deinem separaten Sitemap-Plugin übernommen werden. Filter- und Folgeseiten werden nicht als eigene Einsatzberichte behandelt.' => 'Einsatzlyzer deliberately does not create its own sitemap. All published incidents are public, indexable individual pages and can be included by your separate sitemap plugin. Filter and pagination pages are not treated as separate incident reports.',
        'Brandeinsatz' => 'Fire Incident', 'Technische Hilfeleistung' => 'Technical Assistance', 'Fehlalarm' => 'False Alarm', 'Übungseinsatz' => 'Training Exercise',
        'Einsätze insgesamt' => 'Total Incidents', 'Mit Einsatzbild' => 'With Incident Image', 'Ohne Titelbild' => 'Without Featured Image',
        'Ohne Koordinaten' => 'Without Coordinates', 'Unvollständig' => 'Incomplete', 'Kein Bild' => 'No Image', 'Kein Einsatzbild' => 'No Incident Image',
        'vorhanden' => 'available', 'fehlt' => 'missing', 'Fehlt' => 'Missing', 'Online' => 'Published', 'Prüfung' => 'Pending Review',
        '1 Galeriebild' => '1 Gallery Image', 'Galeriebilder' => 'Gallery Images', 'Titelbild' => 'Featured Image', 'ohne Titelbild' => 'without featured image',
        'Koordinaten vorhanden' => 'Coordinates Available', 'Koordinaten fehlen' => 'Coordinates Missing',
        'Nur unvollständige Einsätze' => 'Incomplete Incidents Only', 'Mit Galerie' => 'With Gallery', 'Ohne Galerie' => 'Without Gallery',
        'Mit Koordinaten' => 'With Coordinates', 'Nur vollständige Einsätze' => 'Complete Incidents Only',
        'Ohne Einsatzbild' => 'Without Incident Image', 'Einsatzübersicht' => 'Incident Overview', 'offen' => 'open',
        'Nr.' => 'No.', 'Karte' => 'Map'
    );
}


register_activation_hook( __FILE__, 'ffl_activate_plugin' );
function ffl_activate_plugin() {
    ffl_register_post_type();

    $terms = array( 'Brandeinsatz', 'Technische Hilfeleistung', 'Fehlalarm', 'Übungseinsatz' );
    foreach ( $terms as $term_name ) {
        if ( ! term_exists( $term_name, 'ffl_einsatzart' ) ) {
            wp_insert_term( $term_name, 'ffl_einsatzart' );
        }
    }

    update_option( 'ffl_map_provider', 'osm' );
    delete_option( 'ffl_einsatzgebiet_enabled' );
    delete_option( 'ffl_einsatzgebiet_style' );
    delete_option( 'ffl_einsatzgebiet_line_width' );
    delete_option( 'ffl_einsatzgebiet_fill_opacity' );
    if ( 'here' === (string) get_option( 'ffl_distance_mode', 'air' ) ) {
        update_option( 'ffl_distance_mode', 'air' );
    }
    delete_option( 'ffl_google_maps_api_key' );
    delete_option( 'ffl_google_map_type' );
    delete_option( 'ffl_here_api_key' );
    delete_option( 'ffl_here_map_style' );

    ffl_migrate_legacy_reports();
    ffl_repair_invalid_einsatz_slugs();
    ffl_maybe_set_archive_page_option();
    update_option( 'ffl_einsatzlyzer_version', FFL_EINSATZLYZER_VERSION );
    flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'ffl_deactivate_plugin' );
function ffl_deactivate_plugin() {
    flush_rewrite_rules();
}

add_action( 'admin_init', 'ffl_maybe_upgrade' );
function ffl_maybe_upgrade() {
    $installed_version = (string) get_option( 'ffl_einsatzlyzer_version', '' );
    if ( version_compare( $installed_version, FFL_EINSATZLYZER_VERSION, '<' ) ) {
        // Seit 9.3.0 steuert ausschließlich das aktive Theme beziehungsweise
        // dessen Theme Builder (z. B. Jeg Kit/Elementor) den Header. Alte
        // Einsatzlyzer-Headeroptionen werden nur neutralisiert und nicht mehr ausgegeben.
        update_option( 'ffl_header_mode', 'theme' );
        // Seit 9.4.1 wird ausschließlich OpenStreetMap/Leaflet verwendet.
        update_option( 'ffl_map_provider', 'osm' );
        delete_option( 'ffl_einsatzgebiet_enabled' );
        delete_option( 'ffl_einsatzgebiet_style' );
        delete_option( 'ffl_einsatzgebiet_line_width' );
        delete_option( 'ffl_einsatzgebiet_fill_opacity' );
        if ( 'here' === (string) get_option( 'ffl_distance_mode', 'air' ) ) {
            update_option( 'ffl_distance_mode', 'air' );
        }
        delete_option( 'ffl_google_maps_api_key' );
        delete_option( 'ffl_google_map_type' );
        delete_option( 'ffl_here_api_key' );
        delete_option( 'ffl_here_map_style' );
        ffl_migrate_legacy_reports();
        ffl_maybe_set_archive_page_option();
        $slug_report = ffl_repair_invalid_einsatz_slugs();
        if ( ! empty( $slug_report ) ) {
            set_transient( 'ffl_slug_repair_notice', $slug_report, 5 * MINUTE_IN_SECONDS );
            flush_rewrite_rules( false );
        }
        update_option( 'ffl_einsatzlyzer_version', FFL_EINSATZLYZER_VERSION );
    }
}

/**
 * Erkennt alte oder generische Permalinks, die bei früheren Importen entstanden sind.
 * Beispiele: /14207/, /page/, /page-2/ oder /seite/.
 */
function ffl_is_invalid_einsatz_slug( $slug ) {
    $slug = sanitize_title( (string) $slug );
    if ( '' === $slug ) {
        return true;
    }

    return (bool) preg_match( '/^(?:\d+|page(?:-\d+)?|seite(?:-\d+)?|einsatz(?:-\d+)?)$/i', $slug );
}

/**
 * Baut einen aussagekräftigen Slug. Der Titel hat Vorrang; nur bei alten
 * generischen Titeln werden Einsatzdaten als sichere Ersatzbasis verwendet.
 */
function ffl_build_einsatz_slug( $post_id, $title = '' ) {
    $post_id = absint( $post_id );
    $title   = trim( wp_strip_all_tags( (string) $title ) );
    $base    = sanitize_title( $title );

    if ( ffl_is_invalid_einsatz_slug( $base ) ) {
        $parts   = array();
        $keyword = trim( (string) get_post_meta( $post_id, '_ffl_alarmstichwort', true ) );
        $place   = trim( (string) get_post_meta( $post_id, '_ffl_einsatzort', true ) );
        $alarm   = trim( (string) get_post_meta( $post_id, '_ffl_alarmzeit', true ) );

        if ( $keyword ) {
            $parts[] = $keyword;
        }
        if ( $place ) {
            $parts[] = $place;
        }
        if ( $alarm ) {
            $timestamp = strtotime( $alarm );
            if ( $timestamp ) {
                $parts[] = wp_date( 'Y-m-d', $timestamp );
            }
        }

        $base = sanitize_title( implode( ' ', $parts ) );
    }

    if ( ffl_is_invalid_einsatz_slug( $base ) ) {
        $base = 'einsatzbericht-' . max( 1, $post_id );
    }

    $post_status = get_post_status( $post_id );
    if ( ! $post_status ) {
        $post_status = 'publish';
    }

    return wp_unique_post_slug( $base, $post_id, $post_status, 'ffl_einsatz', 0 );
}

/**
 * Repariert ausschließlich eindeutig fehlerhafte alte Slugs. Gute bestehende
 * URLs bleiben unverändert. WordPress erhält den alten Slug für eine 301-Weiterleitung.
 */
function ffl_repair_invalid_einsatz_slugs() {
    $ids = get_posts(
        array(
            'post_type'              => 'ffl_einsatz',
            'post_status'            => array( 'publish', 'draft', 'pending', 'private', 'future' ),
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        )
    );

    $changed = array();
    foreach ( $ids as $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post || ! ffl_is_invalid_einsatz_slug( $post->post_name ) ) {
            continue;
        }

        $old_slug = (string) $post->post_name;
        $new_slug = ffl_build_einsatz_slug( $post_id, $post->post_title );
        if ( '' === $new_slug || $new_slug === $old_slug ) {
            continue;
        }

        if ( '' !== $old_slug ) {
            add_post_meta( $post_id, '_wp_old_slug', $old_slug, false );
        }

        $result = wp_update_post(
            array(
                'ID'        => $post_id,
                'post_name' => $new_slug,
            ),
            true
        );

        if ( ! is_wp_error( $result ) ) {
            $changed[] = array(
                'id'   => $post_id,
                'old'  => $old_slug,
                'new'  => $new_slug,
                'title'=> get_the_title( $post_id ),
            );
        }
    }

    return $changed;
}

/**
 * Verhindert bei neuen oder erneut gespeicherten Einsätzen generische Slugs.
 */
add_filter( 'wp_insert_post_data', 'ffl_prevent_invalid_einsatz_slug', 20, 2 );
function ffl_prevent_invalid_einsatz_slug( $data, $postarr ) {
    if ( 'ffl_einsatz' !== ( $data['post_type'] ?? '' ) || 'auto-draft' === ( $data['post_status'] ?? '' ) ) {
        return $data;
    }

    $slug = (string) ( $data['post_name'] ?? '' );
    if ( ! ffl_is_invalid_einsatz_slug( $slug ) ) {
        return $data;
    }

    $post_id           = absint( $postarr['ID'] ?? 0 );
    $data['post_name'] = ffl_build_einsatz_slug( $post_id, (string) ( $data['post_title'] ?? '' ) );
    return $data;
}

add_action( 'admin_notices', 'ffl_show_slug_repair_notice' );
function ffl_show_slug_repair_notice() {
    $report = get_transient( 'ffl_slug_repair_notice' );
    if ( empty( $report ) || ! is_array( $report ) ) {
        return;
    }
    delete_transient( 'ffl_slug_repair_notice' );
    echo '<div class="notice notice-success is-dismissible"><p><strong>Einsatzlyzer:</strong> ' . esc_html( count( $report ) ) . ' fehlerhafte Einsatz-Links wurden automatisch in sprechende URLs umgewandelt. Alte URLs werden von WordPress weitergeleitet. Bitte die manuelle Sitemap neu erzeugen.</p></div>';
}

/**
 * Übernimmt alte Berichte aus _ffl_einsatzbericht in den normalen WordPress-Inhalt.
 * Bestehender post_content wird niemals überschrieben.
 */
function ffl_migrate_legacy_reports() {
    $ids = get_posts(
        array(
            'post_type'              => 'ffl_einsatz',
            'post_status'            => array( 'publish', 'draft', 'pending', 'private', 'future' ),
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        )
    );

    foreach ( $ids as $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post || trim( (string) $post->post_content ) !== '' ) {
            continue;
        }

        $legacy_report = get_post_meta( $post_id, '_ffl_einsatzbericht', true );
        if ( trim( (string) $legacy_report ) === '' ) {
            continue;
        }

        wp_update_post(
            array(
                'ID'           => $post_id,
                'post_content' => wp_kses_post( $legacy_report ),
            )
        );
    }
}

add_action( 'init', 'ffl_register_post_type', 0 );
function ffl_register_post_type() {
    $labels = array(
        'name'               => ffl_lang( 'Einsätze', 'Incidents' ),
        'singular_name'      => ffl_lang( 'Einsatz', 'Incident' ),
        'menu_name'          => 'Einsatzlyzer',
        'name_admin_bar'     => 'Einsatz',
        'all_items'          => ffl_lang( 'Alle Einsätze', 'All Incidents' ),
        'add_new'            => ffl_lang( 'Neu erstellen', 'Add New' ),
        'add_new_item'       => ffl_lang( 'Neuen Einsatz erstellen', 'Add New Incident' ),
        'edit_item'          => ffl_lang( 'Einsatz bearbeiten', 'Edit Incident' ),
        'new_item'           => ffl_lang( 'Neuer Einsatz', 'New Incident' ),
        'view_item'          => ffl_lang( 'Einsatz ansehen', 'View Incident' ),
        'view_items'         => ffl_lang( 'Einsätze ansehen', 'View Incidents' ),
        'search_items'       => ffl_lang( 'Einsätze durchsuchen', 'Search Incidents' ),
        'not_found'          => ffl_lang( 'Keine Einsätze gefunden', 'No incidents found' ),
        'not_found_in_trash' => ffl_lang( 'Keine Einsätze im Papierkorb gefunden', 'No incidents found in Trash' ),
        'featured_image'     => ffl_lang( 'Einsatzbild', 'Incident Image' ),
        'set_featured_image' => ffl_lang( 'Einsatzbild festlegen', 'Set Incident Image' ),
        'archives'           => ffl_lang( 'Einsatzarchive', 'Incident Archives' ),
        'attributes'         => ffl_lang( 'Einsatz-Eigenschaften', 'Incident Attributes' ),
    );

    register_post_type(
        'ffl_einsatz',
        array(
            'labels'             => $labels,
            'public'             => true,
            'hierarchical'       => false,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'menu_position'      => 20,
            'menu_icon'          => FFL_EINSATZLYZER_URL . 'images/branding/einsatzlyzer-menu-icon.png',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' ),
            'has_archive'        => 'feuerwehr-einsaetze',
            'rewrite'            => array(
                'slug'       => 'feuerwehr-einsaetze',
                'with_front' => false,
            ),
            'query_var'          => true,
            'exclude_from_search'=> false,
            'show_in_nav_menus'  => true,
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
        )
    );

    register_taxonomy(
        'ffl_einsatzart',
        array( 'ffl_einsatz' ),
        array(
            'labels'            => array(
                'name'          => ffl_lang( 'Einsatzarten', 'Incident Types' ),
                'singular_name' => ffl_lang( 'Einsatzart', 'Incident Type' ),
                'menu_name'     => ffl_lang( 'Einsatzarten', 'Incident Types' ),
            ),
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'einsatzart' ),
        )
    );

    ffl_register_meta_fields();
}

function ffl_register_meta_fields() {
    $text_fields = array(
        '_ffl_alarmzeit',
        '_ffl_endezeit',
        '_ffl_einsatzleiter',
        '_ffl_einsatzort',
        '_ffl_lat',
        '_ffl_lon',
        '_ffl_manuelle_einsatznummer',
        '_ffl_gallery_ids',
        '_ffl_bildquelle',
        '_ffl_alarmstichwort',
        '_ffl_kurzfassung',
        '_ffl_fahrzeuge',
        '_ffl_einheiten',
        '_ffl_organisationen',
        '_ffl_einsatzkraefte',
        '_ffl_timeline',
        '_ffl_location_privacy',
    );

    foreach ( $text_fields as $meta_key ) {
        register_post_meta(
            'ffl_einsatz',
            $meta_key,
            array(
                'single'            => true,
                'type'              => 'string',
                'show_in_rest'      => true,
                'sanitize_callback' => 'sanitize_textarea_field',
                'auth_callback'     => function() {
                    return current_user_can( 'edit_posts' );
                },
            )
        );
    }
}


/**
 * Liefert die WordPress-Seite, auf der der Einsatzlyzer-Shortcode eingebunden ist.
 * Eine manuelle Auswahl hat Vorrang. Ohne Auswahl wird zuerst die bekannte Seite
 * /einsaetze-der-feuerwehr/ und danach jede veröffentlichte Shortcode-Seite gesucht.
 */
function ffl_detect_archive_page_id() {
    $preferred = get_page_by_path( 'einsaetze-der-feuerwehr', OBJECT, 'page' );
    if ( $preferred instanceof WP_Post && 'publish' === $preferred->post_status ) {
        return (int) $preferred->ID;
    }

    $pages = get_posts(
        array(
            'post_type'              => 'page',
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'orderby'                => 'menu_order title',
            'order'                  => 'ASC',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        )
    );

    foreach ( $pages as $page ) {
        if ( has_shortcode( (string) $page->post_content, 'ffl_einsatz_liste_komplett' ) ) {
            return (int) $page->ID;
        }
    }

    return 0;
}

function ffl_get_archive_page_id() {
    $page_id = absint( get_option( 'ffl_archive_page_id', 0 ) );
    if ( $page_id && 'page' === get_post_type( $page_id ) && 'publish' === get_post_status( $page_id ) ) {
        return $page_id;
    }

    return ffl_detect_archive_page_id();
}

function ffl_maybe_set_archive_page_option() {
    $current = absint( get_option( 'ffl_archive_page_id', 0 ) );
    if ( $current && 'page' === get_post_type( $current ) && 'publish' === get_post_status( $current ) ) {
        return $current;
    }

    $detected = ffl_detect_archive_page_id();
    if ( $detected ) {
        update_option( 'ffl_archive_page_id', $detected );
    }
    return $detected;
}

function ffl_get_archive_url() {
    $page_id = ffl_get_archive_page_id();
    if ( $page_id ) {
        $url = get_permalink( $page_id );
        if ( $url ) {
            return $url;
        }
    }

    return get_post_type_archive_link( 'ffl_einsatz' );
}

/**
 * Das technische CPT-Archiv bleibt für WordPress registriert, wird aber auf die
 * gestaltete Elementor-Seite weitergeleitet. Dadurch gibt es nur eine sichtbare
 * Einsatzübersicht und der Zurück-Link landet immer beim richtigen Menü.
 */
add_action( 'template_redirect', 'ffl_redirect_builtin_archive_to_selected_page', 1 );
function ffl_redirect_builtin_archive_to_selected_page() {
    if ( ! is_post_type_archive( 'ffl_einsatz' ) || is_admin() || wp_doing_ajax() || is_preview() ) {
        return;
    }

    $page_id = ffl_get_archive_page_id();
    if ( ! $page_id ) {
        return;
    }

    $target = get_permalink( $page_id );
    if ( $target ) {
        wp_safe_redirect( $target, 301 );
        exit;
    }
}

/**
 * Plugin-Templates laden. Ein Theme kann sie unter /einsatzlyzer/ überschreiben.
 */
add_filter( 'template_include', 'ffl_template_include', 99 );
function ffl_template_include( $template ) {
    if ( is_singular( 'ffl_einsatz' ) ) {
        $theme_template = locate_template( array( 'einsatzlyzer/single-ffl_einsatz.php' ) );
        return $theme_template ? $theme_template : FFL_EINSATZLYZER_DIR . 'single-ffl_einsatz.php';
    }

    if ( is_post_type_archive( 'ffl_einsatz' ) ) {
        $theme_template = locate_template( array( 'einsatzlyzer/archive-ffl_einsatz.php' ) );
        return $theme_template ? $theme_template : FFL_EINSATZLYZER_DIR . 'archive-ffl_einsatz.php';
    }

    return $template;
}

add_filter( 'body_class', 'ffl_body_classes' );
function ffl_body_classes( $classes ) {
    if ( is_singular( 'ffl_einsatz' ) || is_post_type_archive( 'ffl_einsatz' ) ) {
        $classes[] = 'einsatzlyzer-page';
    }
    return $classes;
}

/**
 * Prüft, ob die aktuelle Seite Einsatzlyzer-Frontend-Dateien benötigt.
 */
function ffl_is_frontend_einsatz_request() {
    global $post;

    $has_shortcode = is_a( $post, 'WP_Post' ) && has_shortcode( (string) $post->post_content, 'ffl_einsatz_liste_komplett' );
    return is_singular( 'ffl_einsatz' ) || is_post_type_archive( 'ffl_einsatz' ) || $has_shortcode;
}

/**
 * Bereitet eine manuell ausgewählte Elementor-Vorlage für Einsatz-Einzelseiten
 * rechtzeitig vor wp_head vor. Elementor Pro ist dafür nicht erforderlich.
 */
add_action( 'wp_enqueue_scripts', 'ffl_prepare_manual_single_header_template', 5 );
function ffl_prepare_manual_single_header_template() {
    if ( ! is_singular( 'ffl_einsatz' ) || 'template' !== ffl_get_single_header_mode() ) {
        return;
    }

    $template_id = ffl_get_single_header_template_id();
    if ( ! $template_id || ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance->frontend ) ) {
        return;
    }

    $frontend = \Elementor\Plugin::$instance->frontend;

    if ( method_exists( $frontend, 'enqueue_styles' ) ) {
        $frontend->enqueue_styles();
    }
    if ( method_exists( $frontend, 'enqueue_scripts' ) ) {
        $frontend->enqueue_scripts();
    }

    if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
        try {
            $css_file = new \Elementor\Core\Files\CSS\Post( $template_id );
            $css_file->enqueue();
        } catch ( Throwable $error ) {
            // Unterschiedliche Elementor-Versionen: Ausgabe bleibt trotzdem möglich.
        }
    }

    $content = $frontend->get_builder_content_for_display( $template_id, true );
    if ( trim( (string) $content ) !== '' ) {
        $GLOBALS['ffl_manual_single_header_markup'] = $content;
    }
}

function ffl_render_manual_single_header_template() {
    if ( ! is_singular( 'ffl_einsatz' ) || 'template' !== ffl_get_single_header_mode() ) {
        return false;
    }

    $template_id = ffl_get_single_header_template_id();
    if ( ! $template_id ) {
        return false;
    }

    $content = isset( $GLOBALS['ffl_manual_single_header_markup'] ) ? (string) $GLOBALS['ffl_manual_single_header_markup'] : '';
    if ( trim( $content ) === '' && class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
        $content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id, true );
    }

    if ( trim( $content ) === '' ) {
        return false;
    }

    echo '<div class="ffl-manual-elementor-header" data-ffl-elementor-template="' . esc_attr( $template_id ) . '">' . $content . '</div>';
    return true;
}


/**
 * Bereitet und rendert optional eine gespeicherte Elementor-Footervorlage am
 * Ende einzelner Einsatzberichte. Elementor Pro ist dafür nicht erforderlich.
 */
add_action( 'wp_enqueue_scripts', 'ffl_prepare_manual_single_footer_template', 6 );
function ffl_prepare_manual_single_footer_template() {
    if ( ! is_singular( 'ffl_einsatz' ) || 'template' !== ffl_get_single_footer_mode() ) {
        return;
    }

    $template_id = ffl_get_single_footer_template_id();
    if ( ! $template_id || ! class_exists( '\Elementor\Plugin' ) || ! isset( \Elementor\Plugin::$instance->frontend ) ) {
        return;
    }

    $frontend = \Elementor\Plugin::$instance->frontend;
    if ( method_exists( $frontend, 'enqueue_styles' ) ) {
        $frontend->enqueue_styles();
    }
    if ( method_exists( $frontend, 'enqueue_scripts' ) ) {
        $frontend->enqueue_scripts();
    }

    if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
        try {
            $css_file = new \Elementor\Core\Files\CSS\Post( $template_id );
            $css_file->enqueue();
        } catch ( Throwable $error ) {
            // Unterschiedliche Elementor-Versionen: Ausgabe bleibt trotzdem möglich.
        }
    }

    $content = $frontend->get_builder_content_for_display( $template_id, true );
    if ( trim( (string) $content ) !== '' ) {
        $GLOBALS['ffl_manual_single_footer_markup'] = $content;
    }
}

function ffl_render_manual_single_footer_template() {
    if ( ! is_singular( 'ffl_einsatz' ) || 'template' !== ffl_get_single_footer_mode() ) {
        return false;
    }

    $template_id = ffl_get_single_footer_template_id();
    if ( ! $template_id ) {
        return false;
    }

    $content = isset( $GLOBALS['ffl_manual_single_footer_markup'] ) ? (string) $GLOBALS['ffl_manual_single_footer_markup'] : '';
    if ( trim( $content ) === '' && class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
        $content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id, true );
    }

    if ( trim( $content ) === '' ) {
        return false;
    }

    echo '<div class="ffl-manual-elementor-footer" data-ffl-elementor-template="' . esc_attr( $template_id ) . '">' . $content . '</div>';
    return true;
}

/**
 * Assets.
 */
add_action( 'wp_enqueue_scripts', 'ffl_enqueue_frontend_assets' );
function ffl_enqueue_frontend_assets() {
    if ( ! ffl_is_frontend_einsatz_request() ) {
        return;
    }

    wp_enqueue_style( 'ffl-einsatzlyzer', FFL_EINSATZLYZER_URL . 'css/einsatzlyzer.css', array(), FFL_EINSATZLYZER_VERSION );
    wp_enqueue_script( 'ffl-einsatzlyzer', FFL_EINSATZLYZER_URL . 'js/einsatzlyzer.js', array(), FFL_EINSATZLYZER_VERSION, true );

    wp_localize_script(
        'ffl-einsatzlyzer',
        'fflEinsatzlyzer',
        array(
            'archiveUrl'      => ffl_get_archive_url(),
            'language'        => ffl_get_plugin_language(),
            'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
            'filterNonce'     => wp_create_nonce( 'ffl_filter_archive' ),
            'copyText'        => ffl_lang( 'Link kopiert', 'Link copied' ),
            'loadingText'     => ffl_lang( 'Einsätze werden geladen …', 'Incidents are loading …' ),
            'mapProvider'     => 'osm',
            'mapTiles'        => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'mapAttribution'  => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            'leafletJsUrl'    => FFL_EINSATZLYZER_URL . 'images/leaflet.js',
            'leafletCssUrl'   => 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            'station'         => ffl_get_station_coordinates(),
            'mapErrorText'    => ffl_lang( 'Die Karte konnte nicht geladen werden.', 'The map could not be loaded.' ),
            'showMapText'     => ffl_lang( 'Einsatzorte auf Karte anzeigen', 'Show Incident Locations on Map' ),
            'closeMapText'    => ffl_lang( 'Karte wieder schließen', 'Close Map' ),
            'discoverMapText' => ffl_lang( 'Einsatzorte auf der Karte entdecken', 'Explore Incident Locations on the Map' ),
            'backReportsText' => ffl_lang( 'Zurück zu den Einsatzberichten', 'Back to Incident Reports' ),
        )
    );
}

/**
 * Hält das Einsatzlyzer-Menüsymbol im gesamten WordPress-Backend kompakt.
 *
 * Die vollständige Admin-CSS wird bewusst nur auf Einsatzlyzer-Seiten geladen.
 * Deshalb braucht das Menüsymbol eine kleine globale Regel, damit WordPress
 * das PNG auch auf Dashboard-, Medien- und anderen Adminseiten nicht in seiner
 * Originalgröße darstellt.
 */
add_action( 'admin_head', 'ffl_render_global_admin_menu_icon_css' );
function ffl_render_global_admin_menu_icon_css() {
    ?>
    <style id="ffl-einsatzlyzer-global-menu-icon-css">
        #adminmenu .menu-icon-ffl_einsatz .wp-menu-image {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 36px !important;
            height: 34px !important;
            overflow: hidden !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        #adminmenu .menu-icon-ffl_einsatz .wp-menu-image img {
            display: block !important;
            width: 20px !important;
            height: 20px !important;
            min-width: 20px !important;
            max-width: 20px !important;
            min-height: 20px !important;
            max-height: 20px !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            object-fit: contain !important;
            object-position: center !important;
            background: transparent !important;
            box-shadow: none !important;
            opacity: .92;
        }
        #adminmenu .menu-icon-ffl_einsatz:hover .wp-menu-image img,
        #adminmenu .menu-icon-ffl_einsatz.wp-has-current-submenu .wp-menu-image img,
        #adminmenu .menu-icon-ffl_einsatz.current .wp-menu-image img {
            opacity: 1;
        }
    </style>
    <?php
}

add_action( 'admin_enqueue_scripts', 'ffl_enqueue_admin_assets' );
function ffl_enqueue_admin_assets( $hook ) {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    $is_einsatz_screen = $screen && 'ffl_einsatz' === $screen->post_type;

    if ( ! $is_einsatz_screen ) {
        return;
    }

    wp_enqueue_style( 'ffl-einsatzlyzer-admin', FFL_EINSATZLYZER_URL . 'css/einsatzlyzer-admin.css', array(), FFL_EINSATZLYZER_VERSION );
    wp_enqueue_script( 'ffl-einsatzlyzer-language', FFL_EINSATZLYZER_URL . 'js/einsatzlyzer-language.js', array(), FFL_EINSATZLYZER_VERSION, true );
    wp_localize_script( 'ffl-einsatzlyzer-language', 'fflEinsatzlyzerLanguage', array( 'language' => ffl_get_plugin_language(), 'translations' => ffl_language_dictionary() ) );

    if ( isset( $_GET['page'] ) && 'ffl_einsatz_einstellungen' === sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
        wp_enqueue_media();
        wp_enqueue_script(
            'ffl-einsatzlyzer-settings-admin',
            FFL_EINSATZLYZER_URL . 'js/einsatzlyzer-settings-admin.js',
            array( 'jquery' ),
            FFL_EINSATZLYZER_VERSION,
            true
        );
    }

    if ( 'edit.php' === $hook ) {
        wp_enqueue_script(
            'ffl-einsatzlyzer-list-admin',
            FFL_EINSATZLYZER_URL . 'js/einsatzlyzer-list-admin.js',
            array(),
            FFL_EINSATZLYZER_VERSION,
            true
        );
        return;
    }

    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_style( 'leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4' );
    wp_enqueue_script( 'leaflet-js', FFL_EINSATZLYZER_URL . 'images/leaflet.js', array(), '1.9.4', true );
    wp_enqueue_script( 'jquery-ui-sortable' );
    wp_enqueue_script( 'ffl-admin-js', FFL_EINSATZLYZER_URL . 'js/einsatz-manager-admin.js', array( 'jquery', 'leaflet-js', 'jquery-ui-sortable' ), FFL_EINSATZLYZER_VERSION, true );

    wp_localize_script(
        'ffl-admin-js',
        'ffl_einsatz_admin_data',
        array(
            'fw_lat'       => '53.269114',
            'fw_lon'       => '7.668382',
            'ajax_url'     => admin_url( 'admin-ajax.php' ),
            'geocode_nonce'=> wp_create_nonce( 'ffl_geocode' ),
        )
    );
}

/**
 * Kompakter Marken-Kopf auf der Einsatzübersicht.
 */
add_action( 'admin_notices', 'ffl_render_admin_brand_header' );
function ffl_render_admin_brand_header() {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen || 'edit-ffl_einsatz' !== $screen->id ) {
        return;
    }
    ?>
    <div class="ffl-admin-title-logo" aria-label="Einsatzlyzer">
        <img src="<?php echo esc_url( FFL_EINSATZLYZER_URL . 'images/branding/einsatzlyzer-logo.png' ); ?>" alt="Einsatzlyzer">
    </div>
    <?php
}

/**
 * Metabox.
 */
add_action( 'add_meta_boxes', 'ffl_add_meta_boxes' );
function ffl_add_meta_boxes() {
    add_meta_box( 'ffl_einsatz_details_meta_box', 'Einsatzdaten', 'ffl_render_einsatz_details_meta_box', 'ffl_einsatz', 'normal', 'high' );
}

function ffl_meta_value( $post_id, $key, $default = '' ) {
    $value = get_post_meta( $post_id, $key, true );
    return $value !== '' ? $value : $default;
}

function ffl_render_einsatz_details_meta_box( $post ) {
    wp_nonce_field( 'ffl_save_einsatz_details', 'ffl_einsatz_details_nonce' );

    $alarmzeit         = ffl_meta_value( $post->ID, '_ffl_alarmzeit' );
    $endezeit          = ffl_meta_value( $post->ID, '_ffl_endezeit' );
    $einsatzleiter     = ffl_meta_value( $post->ID, '_ffl_einsatzleiter' );
    $manuelle_nummer   = ffl_meta_value( $post->ID, '_ffl_manuelle_einsatznummer' );
    $alarmstichwort    = ffl_meta_value( $post->ID, '_ffl_alarmstichwort' );
    $kurzfassung       = ffl_meta_value( $post->ID, '_ffl_kurzfassung' );
    $fahrzeuge         = ffl_meta_value( $post->ID, '_ffl_fahrzeuge' );
    $einheiten         = ffl_meta_value( $post->ID, '_ffl_einheiten' );
    $organisationen    = ffl_meta_value( $post->ID, '_ffl_organisationen' );
    $einsatzkraefte    = ffl_meta_value( $post->ID, '_ffl_einsatzkraefte' );
    $timeline          = ffl_meta_value( $post->ID, '_ffl_timeline' );
    $gallery_ids       = ffl_meta_value( $post->ID, '_ffl_gallery_ids' );
    $bildquelle        = ffl_meta_value( $post->ID, '_ffl_bildquelle' );
    $einsatzort        = ffl_meta_value( $post->ID, '_ffl_einsatzort' );
    $lat               = ffl_meta_value( $post->ID, '_ffl_lat' );
    $lon               = ffl_meta_value( $post->ID, '_ffl_lon' );
    $location_privacy  = ffl_meta_value( $post->ID, '_ffl_location_privacy', 'exact' );
    ?>
    <div class="ffl-admin-grid">
        <section class="ffl-admin-panel ffl-admin-panel--wide">
            <div class="ffl-admin-panel__heading">
                <span class="dashicons dashicons-clock"></span>
                <div><h3>Grunddaten</h3><p>Die wichtigsten Angaben für Titelbereich, Statistik und Suchmaschinen.</p></div>
            </div>
            <div class="ffl-admin-fields ffl-admin-fields--two">
                <label><span>Alarmierungszeit</span><input type="datetime-local" name="ffl_alarmzeit" value="<?php echo esc_attr( $alarmzeit ); ?>"></label>
                <label><span>Einsatzende</span><input type="datetime-local" name="ffl_endezeit" value="<?php echo esc_attr( $endezeit ); ?>"></label>
                <label><span>Alarmstichwort</span><input type="text" name="ffl_alarmstichwort" value="<?php echo esc_attr( $alarmstichwort ); ?>" placeholder="z. B. TH1Y oder F3"></label>
                <label><span>Manuelle Einsatznummer</span><input type="text" name="ffl_manuelle_einsatznummer" value="<?php echo esc_attr( $manuelle_nummer ); ?>" placeholder="Optional"></label>
                <label><span>Einsatzleiter</span><input type="text" name="ffl_einsatzleiter" value="<?php echo esc_attr( $einsatzleiter ); ?>"></label>
                <label><span>Anzahl Einsatzkräfte</span><input type="number" min="0" name="ffl_einsatzkraefte" value="<?php echo esc_attr( $einsatzkraefte ); ?>"></label>
            </div>
            <label class="ffl-admin-field-full"><span>Kurzfassung</span><textarea name="ffl_kurzfassung" rows="3" maxlength="420" placeholder="Kurze Zusammenfassung für Übersicht, Google und den Einstieg in den Bericht."><?php echo esc_textarea( $kurzfassung ); ?></textarea></label>
            <p class="description">Der vollständige Einsatzbericht wird im großen WordPress-Editor oberhalb dieser Box geschrieben. Alte Berichte wurden automatisch übernommen.</p>
        </section>

        <section class="ffl-admin-panel">
            <div class="ffl-admin-panel__heading"><span class="dashicons dashicons-groups"></span><div><h3>Kräfte &amp; Mittel</h3><p>Optionale Angaben werden nur angezeigt, wenn sie ausgefüllt sind.</p></div></div>
            <label><span>Fahrzeuge</span><textarea name="ffl_fahrzeuge" rows="3" placeholder="LF 10, TLF 3000, MTF"><?php echo esc_textarea( $fahrzeuge ); ?></textarea></label>
            <label><span>Beteiligte Feuerwehren / Einheiten</span><textarea name="ffl_einheiten" rows="3" placeholder="Lammertsfehn, Filsum, Detern-Stickhausen-Velde"><?php echo esc_textarea( $einheiten ); ?></textarea></label>
            <label><span>Weitere Organisationen</span><textarea name="ffl_organisationen" rows="3" placeholder="Rettungsdienst, Polizei, Christoph 26"><?php echo esc_textarea( $organisationen ); ?></textarea></label>
        </section>

        <section class="ffl-admin-panel">
            <div class="ffl-admin-panel__heading"><span class="dashicons dashicons-list-view"></span><div><h3>Einsatzverlauf</h3><p>Optional eine Zeile pro Zeitpunkt.</p></div></div>
            <label><span>Zeitleiste</span><textarea name="ffl_timeline" rows="8" placeholder="18:42 | Alarmierung\n18:49 | Eintreffen an der Einsatzstelle\n20:10 | Einsatz beendet"><?php echo esc_textarea( $timeline ); ?></textarea></label>
            <p class="description">Format: <code>Uhrzeit | Ereignis</code>. Alte Einsätze funktionieren ohne Zeitleiste.</p>
        </section>

        <section class="ffl-admin-panel ffl-admin-panel--wide">
            <div class="ffl-admin-panel__heading"><span class="dashicons dashicons-format-gallery"></span><div><h3>Bildergalerie</h3><p>Bilder auswählen, per Ziehen sortieren und mit einer Quelle versehen.</p></div></div>
            <div id="ffl-gallery-preview-container" class="ffl-gallery-admin-preview">
                <?php
                if ( $gallery_ids ) {
                    foreach ( array_filter( array_map( 'absint', explode( ',', $gallery_ids ) ) ) as $attachment_id ) {
                        $thumb = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
                        if ( $thumb ) {
                            echo '<div class="gallery-thumb-wrapper" data-attachment-id="' . esc_attr( $attachment_id ) . '"><img src="' . esc_url( $thumb ) . '" alt=""><button type="button" class="remove-gallery-image" aria-label="Bild entfernen">&times;</button><span class="dashicons dashicons-move"></span></div>';
                        }
                    }
                }
                ?>
            </div>
            <input type="hidden" id="ffl_gallery_ids" name="ffl_gallery_ids" value="<?php echo esc_attr( $gallery_ids ); ?>">
            <button type="button" id="ffl_upload_gallery_button" class="button button-primary">Bilder auswählen</button>
            <label class="ffl-admin-field-full"><span>Bildquelle / Credits</span><input type="text" name="ffl_bildquelle" value="<?php echo esc_attr( $bildquelle ); ?>" placeholder="z. B. Feuerwehr Lammertsfehn"></label>
        </section>

        <section class="ffl-admin-panel ffl-admin-panel--wide">
            <div class="ffl-admin-panel__heading"><span class="dashicons dashicons-location-alt"></span><div><h3>Einsatzort &amp; Karte</h3><p>Die öffentliche Genauigkeit lässt sich für sensible Einsatzstellen begrenzen.</p></div></div>
            <div class="ffl-admin-fields ffl-admin-fields--two">
                <label><span>Einsatzort</span><input type="text" id="ffl_einsatzort" name="ffl_einsatzort" value="<?php echo esc_attr( $einsatzort ); ?>" placeholder="Straße, Ort oder allgemeine Ortsangabe"></label>
                <label><span>Öffentliche Kartendarstellung</span>
                    <select name="ffl_location_privacy">
                        <option value="exact" <?php selected( $location_privacy, 'exact' ); ?>>Genaue Position</option>
                        <option value="approx" <?php selected( $location_privacy, 'approx' ); ?>>Ungefähre Position</option>
                        <option value="hidden" <?php selected( $location_privacy, 'hidden' ); ?>>Keine öffentliche Karte</option>
                    </select>
                </label>
                <label><span>Latitude</span><input type="text" id="ffl_lat" name="ffl_lat" value="<?php echo esc_attr( $lat ); ?>"></label>
                <label><span>Longitude</span><input type="text" id="ffl_lon" name="ffl_lon" value="<?php echo esc_attr( $lon ); ?>"></label>
            </div>
            <div class="ffl-admin-map-actions">
                <button type="button" id="ffl-geocode-and-show-map-button" class="button button-primary">Adresse suchen und Karte öffnen</button>
                <span id="ffl-geocode-status" aria-live="polite"></span>
            </div>
            <div id="ffl-admin-map-popup-wrapper" aria-hidden="true">
                <div id="ffl-admin-map-overlay"></div>
                <div id="ffl-admin-map-dialog" role="dialog" aria-modal="true" aria-label="Einsatzposition festlegen">
                    <div class="ffl-admin-map-dialog__bar"><strong>Einsatzposition festlegen</strong><button type="button" class="ffl-admin-map-close" aria-label="Karte schließen">&times;</button></div>
                    <div id="ffl-admin-map"></div>
                    <div class="ffl-admin-map-dialog__footer"><span>Marker anklicken, verschieben oder direkt in die Karte klicken.</span><button type="button" class="button button-primary ffl-admin-map-close">Position übernehmen</button></div>
                </div>
            </div>
        </section>

        <section class="ffl-admin-panel ffl-admin-panel--wide">
            <div class="ffl-admin-panel__heading"><span class="dashicons dashicons-admin-links"></span><div><h3>Weiterführende Links</h3><p>Zum Beispiel Presseberichte oder Berichte anderer Feuerwehren.</p></div></div>
            <div class="ffl-admin-fields ffl-admin-fields--two">
                <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                    <label><span>Link <?php echo esc_html( $i ); ?></span><input type="url" name="ffl_link_<?php echo esc_attr( $i ); ?>" value="<?php echo esc_url( get_post_meta( $post->ID, '_ffl_link_' . $i, true ) ); ?>" placeholder="https://"></label>
                <?php endfor; ?>
            </div>
        </section>
    </div>
    <?php
}

add_action( 'save_post_ffl_einsatz', 'ffl_save_einsatz_details' );
function ffl_save_einsatz_details( $post_id ) {
    if ( ! isset( $_POST['ffl_einsatz_details_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ffl_einsatz_details_nonce'] ) ), 'ffl_save_einsatz_details' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $text_fields = array(
        'ffl_alarmzeit', 'ffl_endezeit', 'ffl_einsatzleiter', 'ffl_einsatzort', 'ffl_lat', 'ffl_lon',
        'ffl_manuelle_einsatznummer', 'ffl_gallery_ids', 'ffl_bildquelle', 'ffl_alarmstichwort',
        'ffl_einsatzkraefte', 'ffl_location_privacy',
    );
    foreach ( $text_fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, '_' . $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
        }
    }

    $textarea_fields = array( 'ffl_kurzfassung', 'ffl_fahrzeuge', 'ffl_einheiten', 'ffl_organisationen', 'ffl_timeline' );
    foreach ( $textarea_fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, '_' . $field, sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) );
        }
    }

    for ( $i = 1; $i <= 5; $i++ ) {
        $field = 'ffl_link_' . $i;
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, '_' . $field, esc_url_raw( wp_unslash( $_POST[ $field ] ) ) );
        }
    }

    $post = get_post( $post_id );
    if ( $post ) {
        update_post_meta( $post_id, '_ffl_einsatzbericht', wp_kses_post( (string) $post->post_content ) );
    }
}

/**
 * Geocoding serverseitig mit Cache statt direktem Browserzugriff.
 */
add_action( 'wp_ajax_ffl_geocode', 'ffl_ajax_geocode' );
function ffl_ajax_geocode() {
    check_ajax_referer( 'ffl_geocode', 'nonce' );
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( array( 'message' => 'Keine Berechtigung.' ), 403 );
    }

    $address = isset( $_POST['address'] ) ? sanitize_text_field( wp_unslash( $_POST['address'] ) ) : '';
    if ( $address === '' ) {
        wp_send_json_error( array( 'message' => 'Bitte zuerst einen Einsatzort eingeben.' ), 400 );
    }

    $cache_key = 'ffl_geo_' . md5( strtolower( $address ) );
    $cached    = get_transient( $cache_key );
    if ( is_array( $cached ) ) {
        wp_send_json_success( $cached );
    }

    $url = add_query_arg(
        array(
            'format'  => 'jsonv2',
            'limit'   => 1,
            'countrycodes' => 'de',
            'q'       => $address,
        ),
        'https://nominatim.openstreetmap.org/search'
    );

    $response = wp_remote_get(
        $url,
        array(
            'timeout' => 12,
            'headers' => array(
                'User-Agent' => 'Einsatzlyzer/' . FFL_EINSATZLYZER_VERSION . '; ' . home_url( '/' ),
                'Accept-Language' => 'de',
            ),
        )
    );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => 'Die Adresssuche ist momentan nicht erreichbar.' ), 502 );
    }

    $data = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( empty( $data[0]['lat'] ) || empty( $data[0]['lon'] ) ) {
        wp_send_json_error( array( 'message' => 'Für diese Adresse wurde keine Position gefunden.' ), 404 );
    }

    $result = array(
        'lat'          => (float) $data[0]['lat'],
        'lon'          => (float) $data[0]['lon'],
        'display_name' => sanitize_text_field( $data[0]['display_name'] ?? $address ),
    );
    set_transient( $cache_key, $result, DAY_IN_SECONDS );
    wp_send_json_success( $result );
}

/**
 * Einstellungen und Import/Export-Hilfe.
 */
add_action( 'admin_menu', 'ffl_add_admin_menu' );
function ffl_add_admin_menu() {
    add_submenu_page( 'edit.php?post_type=ffl_einsatz', ffl_lang( 'Einstellungen', 'Settings' ), ffl_lang( 'Einstellungen', 'Settings' ), 'manage_options', 'ffl_einsatz_einstellungen', 'ffl_render_einstellungen_page' );
    add_submenu_page( 'edit.php?post_type=ffl_einsatz', 'Import / Export', 'Import / Export', 'manage_options', 'ffl_einsatz_impex', 'ffl_render_impex_page' );
}

add_action( 'admin_init', 'ffl_register_settings' );
function ffl_register_settings() {
    register_setting( 'ffl_einsatz_options_group', 'ffl_plugin_language', array( 'sanitize_callback' => 'ffl_sanitize_plugin_language' ) );
    register_setting( 'ffl_einsatz_options_group', 'ffl_startnummer_aktuelles_jahr', array( 'sanitize_callback' => 'absint' ) );
    register_setting( 'ffl_einsatz_options_group', 'ffl_archive_page_id', array( 'sanitize_callback' => 'absint' ) );
    register_setting( 'ffl_einsatz_options_group', 'ffl_archive_intro', array( 'sanitize_callback' => 'sanitize_textarea_field' ) );
    register_setting( 'ffl_einsatz_options_group', 'ffl_organisation_name', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'ffl_einsatz_options_group', 'ffl_default_image_id', array( 'sanitize_callback' => 'absint' ) );
    register_setting( 'ffl_einsatz_options_group', 'ffl_single_hero_size', array( 'sanitize_callback' => 'ffl_sanitize_single_hero_size' ) );
    register_setting( 'ffl_einsatz_options_group', 'ffl_single_header_mode', array( 'sanitize_callback' => 'ffl_sanitize_single_header_mode' ) );
    register_setting( 'ffl_einsatz_options_group', 'ffl_single_header_template_id', array( 'sanitize_callback' => 'absint' ) );
    register_setting( 'ffl_einsatz_options_group', 'ffl_single_footer_mode', array( 'sanitize_callback' => 'ffl_sanitize_single_footer_mode' ) );
    register_setting( 'ffl_einsatz_options_group', 'ffl_single_footer_template_id', array( 'sanitize_callback' => 'absint' ) );
    register_setting( 'ffl_einsatz_options_group', 'ffl_distance_mode', array( 'sanitize_callback' => 'ffl_sanitize_distance_mode' ) );
    register_setting( 'ffl_einsatz_options_group', 'ffl_station_name', array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'ffl_einsatz_options_group', 'ffl_station_lat', array( 'sanitize_callback' => 'ffl_sanitize_coordinate' ) );
    register_setting( 'ffl_einsatz_options_group', 'ffl_station_lon', array( 'sanitize_callback' => 'ffl_sanitize_coordinate' ) );
}

/**
 * Kopfbereich für Einsatz-Einzelseiten.
 *
 * Ohne Elementor Pro kann eine normale, gespeicherte Elementor-Vorlage
 * ausdrücklich ausgewählt und oberhalb des Einsatzbildes ausgegeben werden.
 * Andere Seiten und Beiträge bleiben vollständig unverändert.
 */
function ffl_sanitize_single_header_mode( $value ) {
    return in_array( $value, array( 'theme', 'template' ), true ) ? $value : 'theme';
}

function ffl_get_single_header_mode() {
    return ffl_sanitize_single_header_mode( (string) get_option( 'ffl_single_header_mode', 'theme' ) );
}

function ffl_get_single_header_template_id() {
    $template_id = absint( get_option( 'ffl_single_header_template_id', 0 ) );
    if ( ! $template_id || 'elementor_library' !== get_post_type( $template_id ) || 'publish' !== get_post_status( $template_id ) ) {
        return 0;
    }
    return $template_id;
}


function ffl_sanitize_single_footer_mode( $value ) {
    return in_array( $value, array( 'theme', 'template' ), true ) ? $value : 'theme';
}

function ffl_get_single_footer_mode() {
    return ffl_sanitize_single_footer_mode( (string) get_option( 'ffl_single_footer_mode', 'theme' ) );
}

function ffl_get_single_footer_template_id() {
    $template_id = absint( get_option( 'ffl_single_footer_template_id', 0 ) );
    if ( ! $template_id || 'elementor_library' !== get_post_type( $template_id ) || 'publish' !== get_post_status( $template_id ) ) {
        return 0;
    }
    return $template_id;
}

function ffl_get_elementor_saved_templates() {
    return get_posts(
        array(
            'post_type'      => 'elementor_library',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        )
    );
}

// Alte Funktionsnamen bleiben als harmlose Kompatibilität erhalten.
function ffl_sanitize_header_mode( $value ) {
    return 'theme';
}

function ffl_get_header_mode() {
    return 'theme';
}

function ffl_sanitize_single_hero_size( $value ) {
    return in_array( $value, array( 'compact', 'standard', 'large' ), true ) ? $value : 'compact';
}

function ffl_get_single_hero_size() {
    return ffl_sanitize_single_hero_size( (string) get_option( 'ffl_single_hero_size', 'compact' ) );
}

function ffl_get_single_hero_height() {
    $sizes = array(
        'compact'  => 350,
        'standard' => 430,
        'large'    => 520,
    );
    $size = ffl_get_single_hero_size();
    return isset( $sizes[ $size ] ) ? $sizes[ $size ] : 350;
}

function ffl_sanitize_map_provider( $value ) {
    return 'osm';
}

function ffl_get_google_maps_api_key() {
    return '';
}

function ffl_get_here_api_key() {
    return '';
}

function ffl_get_map_provider() {
    return 'osm';
}

function ffl_sanitize_google_map_type( $value ) {
    return 'roadmap';
}

function ffl_get_google_map_type() {
    return 'roadmap';
}

function ffl_sanitize_here_map_style( $value ) {
    return 'normal';
}

function ffl_get_here_map_style() {
    return 'normal';
}

function ffl_sanitize_checkbox( $value ) {
    return empty( $value ) ? 0 : 1;
}

function ffl_sanitize_distance_mode( $value ) {
    return in_array( $value, array( 'none', 'air' ), true ) ? $value : 'air';
}

function ffl_get_distance_mode() {
    return ffl_sanitize_distance_mode( (string) get_option( 'ffl_distance_mode', 'air' ) );
}

function ffl_sanitize_coordinate( $value ) {
    $value = str_replace( ',', '.', trim( (string) $value ) );
    return is_numeric( $value ) ? (string) (float) $value : '';
}

function ffl_get_station_coordinates() {
    $lat = (float) get_option( 'ffl_station_lat', '53.269114' );
    $lon = (float) get_option( 'ffl_station_lon', '7.668382' );
    if ( ! $lat || ! $lon ) {
        return null;
    }

    return array(
        'lat'  => $lat,
        'lon'  => $lon,
        'name' => trim( (string) get_option( 'ffl_station_name', 'Feuerwehrhaus Lammertsfehn' ) ) ?: 'Feuerwehrhaus',
    );
}

/**
 * Prüft, ob die gewählte Elementor-Vorlage wirklich existiert.
 */
function ffl_sanitize_elementor_template_id( $value ) {
    $template_id = absint( $value );
    if ( ! $template_id ) {
        return 0;
    }

    return get_post_type( $template_id ) === 'elementor_library' ? $template_id : 0;
}

/**
 * Liefert veröffentlichte Elementor-Vorlagen für die Auswahl im Backend.
 */
function ffl_get_elementor_template_choices() {
    if ( ! post_type_exists( 'elementor_library' ) ) {
        return array();
    }

    return get_posts(
        array(
            'post_type'      => 'elementor_library',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        )
    );
}

/**
 * Wandelt verschachtelte Elementor-Bedingungen in eine durchsuchbare Zeichenkette um.
 */
function ffl_flatten_elementor_conditions( $value ) {
    if ( is_array( $value ) ) {
        $parts = array();
        foreach ( $value as $item ) {
            $parts[] = ffl_flatten_elementor_conditions( $item );
        }
        return implode( ' ', $parts );
    }

    return is_scalar( $value ) ? (string) $value : '';
}

/**
 * Bewertet veröffentlichte Elementor-Vorlagen und findet den wahrscheinlichsten
 * Website-Header. Im Gegensatz zur früheren Logik funktioniert dies auch dann,
 * wenn mehrere Header-, Menü- oder Mobilvorlagen vorhanden sind.
 */
function ffl_find_best_elementor_header_template_id() {
    if ( ! post_type_exists( 'elementor_library' ) ) {
        return 0;
    }

    $templates = get_posts(
        array(
            'post_type'              => 'elementor_library',
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => true,
        )
    );

    $best_id    = 0;
    $best_score = -100000;

    foreach ( $templates as $template_id ) {
        $template_id   = absint( $template_id );
        $template_type = strtolower( (string) get_post_meta( $template_id, '_elementor_template_type', true ) );
        $is_header     = 'header' === $template_type;

        if ( ! $is_header && taxonomy_exists( 'elementor_library_type' ) ) {
            $is_header = has_term( 'header', 'elementor_library_type', $template_id );
        }

        $title      = strtolower( remove_accents( get_the_title( $template_id ) ) );
        $data       = strtolower( (string) get_post_meta( $template_id, '_elementor_data', true ) );
        $conditions = strtolower( ffl_flatten_elementor_conditions( get_post_meta( $template_id, '_elementor_conditions', true ) ) );
        $looks_like_menu = (bool) preg_match( '/\b(header|kopf|menu|menue|navigation|nav)\b/', $title )
            || false !== strpos( $data, 'nav-menu' )
            || false !== strpos( $data, 'wordpress-menu' );

        // Footer und sonstige Vorlagen niemals nur wegen eines zufälligen Wortes wählen.
        if ( ! $is_header && ! $looks_like_menu ) {
            continue;
        }

        $score = 0;
        if ( $is_header ) {
            $score += 140;
        }
        if ( $looks_like_menu ) {
            $score += 45;
        }
        if ( false !== strpos( $conditions, 'include/general' ) ) {
            $score += 120;
        }
        if ( false !== strpos( $conditions, 'include/singular' ) ) {
            $score += 55;
        }
        if ( false !== strpos( $conditions, 'include/archive' ) ) {
            $score += 35;
        }
        if ( false !== strpos( $conditions, 'exclude/general' ) ) {
            $score -= 180;
        }
        if ( false !== strpos( $data, 'nav-menu' ) ) {
            $score += 35;
        }

        // Bei Gleichstand die zuletzt bearbeitete Vorlage bevorzugen.
        $modified = (int) get_post_modified_time( 'U', true, $template_id );
        $score    += min( 20, (int) floor( $modified / 100000000 ) );

        if ( $score > $best_score ) {
            $best_score = $score;
            $best_id    = $template_id;
        }
    }

    return $best_id;
}

/**
 * Alter Funktionsname bleibt als Kompatibilitäts-Hülle erhalten.
 */
function ffl_find_unique_elementor_header_template_id() {
    return ffl_find_best_elementor_header_template_id();
}

function ffl_get_effective_elementor_header_template_id() {
    $selected = absint( get_option( 'ffl_elementor_header_template_id', 0 ) );
    if ( $selected && 'publish' === get_post_status( $selected ) && 'elementor_library' === get_post_type( $selected ) ) {
        return $selected;
    }

    return 0;
}

/**
 * Rendert optional eine gespeicherte Elementor-Kopfvorlage oberhalb der
 * Einsatzübersicht und der Einsatz-Einzelseiten. Das ist besonders für
 * Elementor Free hilfreich, wenn das Menü bisher nur innerhalb einer Seite
 * und nicht als globaler Theme-Header angelegt wurde.
 */
function ffl_render_selected_elementor_header( $template_id = 0 ) {
    static $rendered = false;

    if ( $rendered ) {
        return false;
    }

    $template_id = $template_id ? absint( $template_id ) : ffl_get_effective_elementor_header_template_id();
    if ( ! $template_id || get_post_status( $template_id ) !== 'publish' || get_post_type( $template_id ) !== 'elementor_library' ) {
        return false;
    }

    $content = isset( $GLOBALS['ffl_elementor_header_markup'] ) ? (string) $GLOBALS['ffl_elementor_header_markup'] : '';
    if ( '' === trim( $content ) && class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
        $content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id, true );
    }

    if ( trim( (string) $content ) === '' ) {
        return false;
    }

    $rendered = true;
    echo '<div class="ffl-elementor-header-template elementor-location-header" data-ffl-elementor-header="' . esc_attr( $template_id ) . '">' . $content . '</div>';
    return true;
}

/**
 * Gibt ausschließlich den normalen WordPress-Theme-Kopf aus.
 *
 * Dadurch entscheidet der aktive Theme Builder selbst anhand seiner Bedingungen,
 * welcher Header auf der aktuellen Seite erscheint. Einsatzlyzer rendert keine
 * gespeicherte Elementor-Vorlage mehr zusätzlich und überschreibt damit weder
 * globale noch seiten- oder beitragsspezifische Header-Regeln.
 */
function ffl_render_page_header() {
    get_header();
    ffl_render_manual_single_header_template();
}


add_action( 'admin_post_ffl_repair_einsatz_slugs', 'ffl_handle_manual_slug_repair' );
function ffl_handle_manual_slug_repair() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Keine Berechtigung.' );
    }
    check_admin_referer( 'ffl_repair_einsatz_slugs' );

    $report = ffl_repair_invalid_einsatz_slugs();
    if ( ! empty( $report ) ) {
        flush_rewrite_rules( false );
    }

    $redirect = add_query_arg(
        array(
            'post_type'        => 'ffl_einsatz',
            'page'             => 'ffl_einsatz_einstellungen',
            'ffl_slug_repaired'=> count( $report ),
        ),
        admin_url( 'edit.php' )
    );
    wp_safe_redirect( $redirect );
    exit;
}

function ffl_render_einstellungen_page() {
    $hero_size             = ffl_get_single_hero_size();
    $single_header_mode    = ffl_get_single_header_mode();
    $single_header_id      = ffl_get_single_header_template_id();
    $single_footer_mode    = ffl_get_single_footer_mode();
    $single_footer_id      = ffl_get_single_footer_template_id();
    $archive_page_id       = ffl_get_archive_page_id();
    $archive_pages         = get_pages( array( 'post_status' => 'publish', 'sort_column' => 'post_title' ) );
    $elementor_templates   = ffl_get_elementor_saved_templates();
    $distance_mode         = ffl_get_distance_mode();
    $station               = ffl_get_station_coordinates();
    $default_image_id      = absint( get_option( 'ffl_default_image_id', 0 ) );
    $default_image_url     = $default_image_id ? wp_get_attachment_image_url( $default_image_id, 'medium' ) : '';
    ?>
    <div class="wrap ffl-settings-wrap">
        <div class="ffl-admin-page-brand ffl-admin-page-brand--compact"><img src="<?php echo esc_url( FFL_EINSATZLYZER_URL . 'images/branding/einsatzlyzer-logo.png' ); ?>" alt="Einsatzlyzer"><div><h1><?php echo esc_html( ffl_lang( 'Einsatzlyzer Einstellungen', 'Einsatzlyzer Settings' ) ); ?></h1><p><?php echo esc_html( ffl_lang( 'Darstellung, Navigation und Karten zentral verwalten.', 'Manage display, navigation and maps centrally.' ) ); ?></p></div></div>
        <form method="post" action="options.php">
            <?php settings_fields( 'ffl_einsatz_options_group' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php echo esc_html( ffl_lang( 'Plugin-Sprache', 'Plugin Language' ) ); ?></th>
                    <td>
                        <select name="ffl_plugin_language" class="regular-text">
                            <option value="de" <?php selected( ffl_get_plugin_language(), 'de' ); ?>>Deutsch</option>
                            <option value="en" <?php selected( ffl_get_plugin_language(), 'en' ); ?>>English</option>
                        </select>
                        <p class="description"><?php echo esc_html( ffl_lang( 'Ändert ausschließlich Einsatzlyzer im Backend und Frontend. Deine eingegebenen Einsatzberichte bleiben unverändert.', 'Changes only Einsatzlyzer in the backend and frontend. Your entered incident reports remain unchanged.' ) ); ?></p>
                    </td>
                </tr>
                <tr><th scope="row">Name der Feuerwehr</th><td><input class="regular-text" type="text" name="ffl_organisation_name" value="<?php echo esc_attr( get_option( 'ffl_organisation_name', get_bloginfo( 'name' ) ) ); ?>"></td></tr>
                <tr>
                    <th scope="row">Standardbild für Einsätze ohne Bild</th>
                    <td>
                        <input type="hidden" id="ffl_default_image_id" name="ffl_default_image_id" value="<?php echo esc_attr( $default_image_id ); ?>">
                        <div class="ffl-default-image-picker">
                            <div id="ffl_default_image_preview" class="ffl-default-image-preview<?php echo $default_image_url ? '' : ' is-empty'; ?>">
                                <?php if ( $default_image_url ) : ?>
                                    <img src="<?php echo esc_url( $default_image_url ); ?>" alt="Ausgewähltes Standardbild">
                                <?php else : ?>
                                    <span>Noch kein eigenes Standardbild ausgewählt</span>
                                <?php endif; ?>
                            </div>
                            <p>
                                <button type="button" class="button button-secondary" id="ffl_select_default_image">Bild auswählen</button>
                                <button type="button" class="button" id="ffl_remove_default_image"<?php echo $default_image_id ? '' : ' style="display:none"'; ?>>Auswahl entfernen</button>
                            </p>
                        </div>
                        <p class="description">Dieses Bild wird für Google, soziale Netzwerke und strukturierte Artikeldaten verwendet, wenn ein Einsatz weder Beitragsbild noch Galeriebild besitzt. Das Bild sollte mindestens 1200 × 675 Pixel groß sein.</p>
                    </td>
                </tr>
                <tr><th scope="row">Einleitung Einsatzarchiv</th><td><textarea class="large-text" rows="4" name="ffl_archive_intro"><?php echo esc_textarea( get_option( 'ffl_archive_intro', 'Unsere Einsätze im Überblick – transparent, übersichtlich und mit allen wichtigen Informationen.' ) ); ?></textarea></td></tr>
                <tr>
                    <th scope="row">Seite der Einsatzübersicht</th>
                    <td>
                        <select name="ffl_archive_page_id" class="regular-text">
                            <option value="0">Automatisch erkennen (empfohlen)</option>
                            <?php foreach ( $archive_pages as $archive_page ) : ?>
                                <option value="<?php echo esc_attr( $archive_page->ID ); ?>" <?php selected( $archive_page_id, $archive_page->ID ); ?>><?php echo esc_html( $archive_page->post_title ?: 'Seite #' . $archive_page->ID ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Wähle die Elementor-Seite mit dem Shortcode <code>[ffl_einsatz_liste_komplett]</code>. „Alle Einsätze“, „Zum Einsatzarchiv“ und das technische Archiv führen dann auf diese Seite.</p>
                    </td>
                </tr>
                <tr><th scope="row">Startnummer aktuelles Jahr</th><td><input type="number" min="1" name="ffl_startnummer_aktuelles_jahr" value="<?php echo esc_attr( get_option( 'ffl_startnummer_aktuelles_jahr', 1 ) ); ?>"><p class="description">Wird nur verwendet, wenn keine manuelle Einsatznummer hinterlegt ist.</p></td></tr>
                <tr>
                    <th scope="row">Menü auf Einsatz-Einzelseiten</th>
                    <td>
                        <select name="ffl_single_header_mode" class="regular-text">
                            <option value="theme" <?php selected( $single_header_mode, 'theme' ); ?>>Normalen Theme-Header verwenden</option>
                            <option value="template" <?php selected( $single_header_mode, 'template' ); ?>>Gespeicherte Elementor-Vorlage oberhalb des Einsatzbildes anzeigen</option>
                        </select>
                        <p class="description">Die zweite Auswahl funktioniert auch mit der kostenlosen Elementor-Version. Sie gilt ausschließlich für einzelne Einsatzberichte; normale Seiten und Beiträge bleiben unverändert.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Elementor-Menüvorlage</th>
                    <td>
                        <select name="ffl_single_header_template_id" class="regular-text">
                            <option value="0">Keine Vorlage ausgewählt</option>
                            <?php foreach ( $elementor_templates as $template ) : ?>
                                <option value="<?php echo esc_attr( $template->ID ); ?>" <?php selected( $single_header_id, $template->ID ); ?>><?php echo esc_html( get_the_title( $template ) ?: 'Vorlage #' . $template->ID ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( empty( $elementor_templates ) ) : ?>
                            <p class="description"><strong>Noch keine gespeicherte Elementor-Vorlage vorhanden.</strong> Öffne die Seite „Einsätze“ mit Elementor, klicke den äußersten Container deines originalen Menüs mit der rechten Maustaste an und wähle „Als Vorlage speichern“. Danach erscheint sie hier.</p>
                        <?php else : ?>
                            <p class="description">Wähle hier die Vorlage mit deinem Feuerwehrlogo und dem originalen Menü. Keine automatische Erkennung und keine Pro-Version erforderlich.</p>
                        <?php endif; ?>
                        <p><a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=elementor_library&tabs_group=library' ) ); ?>">Gespeicherte Elementor-Vorlagen öffnen</a></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Footer auf Einsatz-Einzelseiten</th>
                    <td>
                        <select name="ffl_single_footer_mode" class="regular-text">
                            <option value="theme" <?php selected( $single_footer_mode, 'theme' ); ?>>Nur normalen Theme-Footer verwenden</option>
                            <option value="template" <?php selected( $single_footer_mode, 'template' ); ?>>Gespeicherte Elementor-Vorlage am Seitenende anzeigen</option>
                        </select>
                        <p class="description">Die Vorlage wird nur bei einzelnen Einsatzberichten unmittelbar vor dem normalen Theme-Footer ausgegeben.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Elementor-Footervorlage</th>
                    <td>
                        <select name="ffl_single_footer_template_id" class="regular-text">
                            <option value="0">Keine Vorlage ausgewählt</option>
                            <?php foreach ( $elementor_templates as $template ) : ?>
                                <option value="<?php echo esc_attr( $template->ID ); ?>" <?php selected( $single_footer_id, $template->ID ); ?>><?php echo esc_html( get_the_title( $template ) ?: 'Vorlage #' . $template->ID ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Speichere den äußeren Container deines bestehenden Footers in Elementor als Vorlage und wähle ihn hier aus. Elementor Pro ist nicht erforderlich.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Titelbild auf Einsatz-Einzelseiten</th>
                    <td>
                        <select name="ffl_single_hero_size" class="regular-text">
                            <option value="compact" <?php selected( $hero_size, 'compact' ); ?>>Kompakt – ca. 350 px (empfohlen)</option>
                            <option value="standard" <?php selected( $hero_size, 'standard' ); ?>>Normal – ca. 430 px</option>
                            <option value="large" <?php selected( $hero_size, 'large' ); ?>>Groß – ca. 520 px</option>
                        </select>
                        <p class="description">Gilt nur auf PC und größeren Tablets. Auf dem Handy bleibt die bewährte mobile Darstellung erhalten.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Kartenanbieter</th>
                    <td>
                        <strong>OpenStreetMap / Leaflet</strong>
                        <p class="description">Einsatzlyzer verwendet ausschließlich OpenStreetMap. Es wird kein Google- oder HERE-API-Schlüssel benötigt und es muss kein Abrechnungskonto hinterlegt werden.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Entfernung zum Einsatzort</th>
                    <td>
                        <select name="ffl_distance_mode" class="regular-text">
                            <option value="none" <?php selected( $distance_mode, 'none' ); ?>>Nicht anzeigen</option>
                            <option value="air" <?php selected( $distance_mode, 'air' ); ?>>Luftlinie vom Feuerwehrhaus</option>
                        </select>
                        <p class="description">Die Entfernung wird ausschließlich aus den gespeicherten Koordinaten des Feuerwehrhauses und des Einsatzortes berechnet. Der Standort des Besuchers wird nicht abgefragt oder gespeichert.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Ausgangspunkt Feuerwehrhaus</th>
                    <td>
                        <p><input class="regular-text" type="text" name="ffl_station_name" value="<?php echo esc_attr( $station['name'] ?? 'Feuerwehrhaus Lammertsfehn' ); ?>" placeholder="Feuerwehrhaus Lammertsfehn"></p>
                        <p>
                            <label>Breitengrad <input type="text" name="ffl_station_lat" value="<?php echo esc_attr( $station['lat'] ?? '53.269114' ); ?>" inputmode="decimal"></label>
                            &nbsp;&nbsp;
                            <label>Längengrad <input type="text" name="ffl_station_lon" value="<?php echo esc_attr( $station['lon'] ?? '7.668382' ); ?>" inputmode="decimal"></label>
                        </p>
                        <p class="description">Diese Koordinaten sind der feste Startpunkt für die Luftlinienentfernung und den Button „Route in OpenStreetMap planen“.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <?php if ( isset( $_GET['ffl_slug_repaired'] ) ) : ?>
            <div class="notice notice-success inline"><p><strong>Linkprüfung abgeschlossen:</strong> <?php echo esc_html( absint( $_GET['ffl_slug_repaired'] ) ); ?> fehlerhafte Einsatz-Links wurden korrigiert. Bitte deine Sitemap anschließend neu erzeugen.</p></div>
        <?php endif; ?>
        <div class="card" style="max-width:900px;margin-top:20px">
            <h2>Fehlerhafte Einsatz-Links reparieren</h2>
            <p>Prüft alte Einsatz-URLs wie <code>/14207/</code>, <code>/page/</code> oder <code>/seite-2/</code> und ersetzt nur diese eindeutig fehlerhaften Slugs durch sprechende Links aus dem Einsatz-Titel. Gute bestehende URLs bleiben unverändert. Alte Adressen werden als WordPress-Weiterleitung gespeichert.</p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="ffl_repair_einsatz_slugs">
                <?php wp_nonce_field( 'ffl_repair_einsatz_slugs' ); ?>
                <?php submit_button( 'Einsatz-Links jetzt prüfen', 'secondary', 'submit', false ); ?>
            </form>
        </div>
        <div class="notice notice-info inline"><p><strong>Sitemap:</strong> Einsatzlyzer erzeugt bewusst keine eigene Sitemap. Alle veröffentlichten Einsätze sind öffentliche, indexierbare Einzelseiten und können von deinem separaten Sitemap-Plugin übernommen werden. Filter- und Folgeseiten werden nicht als eigene Einsatzberichte behandelt.</p></div>
    </div>
    <?php
}

function ffl_render_impex_page() {
    ffl_impex_render_admin_page();
}

/**
 * Kompakte und aussagekräftige Einsatzübersicht im WordPress-Backend.
 */
add_filter( 'manage_ffl_einsatz_posts_columns', 'ffl_admin_columns' );
function ffl_admin_columns( $columns ) {
    return array(
        'cb'                        => $columns['cb'],
        'ffl_thumb'                 => ffl_lang( 'Bild', 'Image' ),
        'title'                     => ffl_lang( 'Einsatz', 'Incident' ),
        'ffl_number'                => ffl_lang( 'Nr.', 'No.' ),
        'ffl_alarm'                 => ffl_lang( 'Alarmierung', 'Alert' ),
        'taxonomy-ffl_einsatzart'   => ffl_lang( 'Einsatzart', 'Incident Type' ),
        'ffl_location'              => ffl_lang( 'Einsatzort', 'Incident Location' ),
        'ffl_gallery'               => ffl_lang( 'Bilder', 'Images' ),
        'ffl_map'                   => ffl_lang( 'Karte', 'Map' ),
        'ffl_status'                => 'Status',
    );
}

add_action( 'manage_ffl_einsatz_posts_custom_column', 'ffl_admin_column_content', 10, 2 );
function ffl_admin_column_content( $column, $post_id ) {
    if ( 'ffl_thumb' === $column ) {
        $image_id = get_post_thumbnail_id( $post_id );
        if ( ! $image_id ) {
            $gallery = array_values( array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $post_id, '_ffl_gallery_ids', true ) ) ) ) );
            $image_id = $gallery ? (int) $gallery[0] : 0;
        }

        if ( $image_id ) {
            echo wp_get_attachment_image( $image_id, array( 96, 66 ), false, array( 'class' => 'ffl-admin-list-thumb', 'loading' => 'lazy' ) );
        } else {
            echo '<span class="ffl-admin-list-thumb ffl-admin-list-thumb--empty" title="Kein Einsatzbild"><img src="' . esc_url( FFL_EINSATZLYZER_URL . 'images/branding/einsatzlyzer-icon.png' ) . '" alt=""><small>Kein Bild</small></span>';
        }
        return;
    }

    if ( 'ffl_number' === $column ) {
        echo '<strong class="ffl-admin-number">' . esc_html( ffl_get_einsatz_number( $post_id ) ) . '</strong>';
        return;
    }

    if ( 'ffl_alarm' === $column ) {
        $alarm = ffl_meta_value( $post_id, '_ffl_alarmzeit' );
        if ( $alarm ) {
            $timestamp = strtotime( $alarm );
            echo '<span class="ffl-admin-date">' . esc_html( wp_date( 'd.m.Y', $timestamp ) ) . '</span>';
            echo '<span class="ffl-admin-time">' . esc_html( wp_date( 'H:i', $timestamp ) ) . ' Uhr</span>';
        } else {
            echo '<span class="ffl-admin-missing">Fehlt</span>';
        }
        return;
    }

    if ( 'ffl_location' === $column ) {
        $location = trim( (string) ffl_meta_value( $post_id, '_ffl_einsatzort' ) );
        echo $location ? '<span class="ffl-admin-location" title="' . esc_attr( $location ) . '">' . esc_html( $location ) . '</span>' : '<span class="ffl-admin-missing">Fehlt</span>';
        return;
    }

    if ( 'ffl_gallery' === $column ) {
        $gallery = array_values( array_unique( array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $post_id, '_ffl_gallery_ids', true ) ) ) ) ) );
        $featured = has_post_thumbnail( $post_id );
        $count = count( $gallery );
        $label = 1 === $count ? '1 Galeriebild' : $count . ' Galeriebilder';
        echo '<span class="ffl-admin-icon-status ' . ( $count ? 'is-ok' : 'is-muted' ) . '" title="' . esc_attr( $label ) . '"><span class="dashicons dashicons-format-gallery"></span><b>' . esc_html( $count ) . '</b></span>';
        echo '<span class="ffl-admin-mini-state ' . ( $featured ? 'is-ok' : 'is-warning' ) . '">' . ( $featured ? 'Titelbild' : 'ohne Titelbild' ) . '</span>';
        return;
    }

    if ( 'ffl_map' === $column ) {
        $lat = trim( (string) get_post_meta( $post_id, '_ffl_lat', true ) );
        $lon = trim( (string) get_post_meta( $post_id, '_ffl_lon', true ) );
        if ( '' !== $lat && '' !== $lon ) {
            echo '<span class="ffl-admin-icon-status is-ok" title="Koordinaten vorhanden"><span class="dashicons dashicons-location"></span></span><span class="ffl-admin-mini-state is-ok">vorhanden</span>';
        } else {
            echo '<span class="ffl-admin-icon-status is-warning" title="Koordinaten fehlen"><span class="dashicons dashicons-location-alt"></span></span><span class="ffl-admin-mini-state is-warning">fehlt</span>';
        }
        return;
    }

    if ( 'ffl_status' === $column ) {
        $post = get_post( $post_id );
        if ( ! $post ) {
            return;
        }
        $labels = array(
            'publish' => 'Online',
            'draft'   => 'Entwurf',
            'pending' => 'Prüfung',
            'future'  => 'Geplant',
            'private' => 'Privat',
        );
        $label = isset( $labels[ $post->post_status ] ) ? $labels[ $post->post_status ] : ucfirst( $post->post_status );
        echo '<span class="ffl-admin-status ffl-admin-status--' . esc_attr( $post->post_status ) . '">' . esc_html( $label ) . '</span>';
        echo '<span class="ffl-admin-published">' . esc_html( wp_date( 'd.m.Y', get_post_timestamp( $post ) ) ) . '</span>';

        $missing = ffl_admin_missing_fields( $post_id );
        if ( $missing ) {
            echo '<span class="ffl-admin-incomplete" title="Fehlt: ' . esc_attr( implode( ', ', $missing ) ) . '"><span class="dashicons dashicons-warning"></span>' . esc_html( count( $missing ) ) . ' offen</span>';
        }
    }
}

/** Felder, die für eine vollständige Einsatzdarstellung sinnvoll sind. */
function ffl_admin_missing_fields( $post_id ) {
    $missing = array();
    if ( '' === trim( (string) get_the_title( $post_id ) ) ) {
        $missing[] = 'Titel';
    }
    if ( '' === trim( wp_strip_all_tags( (string) ffl_get_report_raw( $post_id ) ) ) ) {
        $missing[] = 'Bericht';
    }
    if ( '' === trim( (string) get_post_meta( $post_id, '_ffl_alarmzeit', true ) ) ) {
        $missing[] = 'Alarmierung';
    }
    if ( '' === trim( (string) get_post_meta( $post_id, '_ffl_einsatzort', true ) ) ) {
        $missing[] = 'Einsatzort';
    }
    if ( '' === trim( (string) get_post_meta( $post_id, '_ffl_lat', true ) ) || '' === trim( (string) get_post_meta( $post_id, '_ffl_lon', true ) ) ) {
        $missing[] = 'Koordinaten';
    }
    $gallery = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $post_id, '_ffl_gallery_ids', true ) ) ) );
    if ( ! has_post_thumbnail( $post_id ) && ! $gallery ) {
        $missing[] = 'Bild';
    }
    return $missing;
}

add_filter( 'manage_edit-ffl_einsatz_sortable_columns', 'ffl_admin_sortable_columns' );
function ffl_admin_sortable_columns( $columns ) {
    $columns['ffl_alarm']    = 'ffl_alarm';
    $columns['ffl_number']   = 'ffl_number';
    $columns['ffl_location'] = 'ffl_location';
    return $columns;
}

add_filter( 'months_dropdown_results', 'ffl_admin_hide_month_dropdown', 10, 2 );
function ffl_admin_hide_month_dropdown( $months, $post_type ) {
    return 'ffl_einsatz' === $post_type ? array() : $months;
}

add_action( 'restrict_manage_posts', 'ffl_admin_filters' );
function ffl_admin_filters( $post_type ) {
    if ( 'ffl_einsatz' !== $post_type ) {
        return;
    }

    global $wpdb;
    $years = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT DISTINCT LEFT(meta_value, 4) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value REGEXP '^[0-9]{4}-' ORDER BY meta_value DESC",
            '_ffl_alarmzeit'
        )
    );
    $selected_year = isset( $_GET['ffl_year'] ) ? absint( $_GET['ffl_year'] ) : 0;
    echo '<select name="ffl_year" class="ffl-admin-filter"><option value="">Alle Jahre</option>';
    foreach ( $years as $year ) {
        if ( ! preg_match( '/^\d{4}$/', (string) $year ) ) {
            continue;
        }
        echo '<option value="' . esc_attr( $year ) . '" ' . selected( $selected_year, (int) $year, false ) . '>' . esc_html( $year ) . '</option>';
    }
    echo '</select>';

    $selected_term = isset( $_GET['ffl_einsatzart_filter'] ) ? sanitize_title( wp_unslash( $_GET['ffl_einsatzart_filter'] ) ) : '';
    wp_dropdown_categories(
        array(
            'show_option_all' => 'Alle Einsatzarten',
            'taxonomy'        => 'ffl_einsatzart',
            'name'            => 'ffl_einsatzart_filter',
            'orderby'         => 'name',
            'selected'        => $selected_term,
            'value_field'     => 'slug',
            'hide_empty'      => false,
            'class'           => 'ffl-admin-filter',
        )
    );

    $selected_state = isset( $_GET['ffl_completeness'] ) ? sanitize_key( $_GET['ffl_completeness'] ) : '';
    $states = array(
        ''                => 'Alle Vollständigkeiten',
        'incomplete'      => 'Nur unvollständige Einsätze',
        'with_image'      => 'Mit Einsatzbild',
        'missing_image'   => 'Ohne Einsatzbild',
        'missing_featured'=> 'Ohne Titelbild',
        'with_gallery'    => 'Mit Galerie',
        'missing_gallery' => 'Ohne Galerie',
        'with_coords'     => 'Mit Koordinaten',
        'missing_coords'  => 'Ohne Koordinaten',
        'complete'        => 'Nur vollständige Einsätze',
    );
    echo '<select name="ffl_completeness" class="ffl-admin-filter">';
    foreach ( $states as $value => $label ) {
        echo '<option value="' . esc_attr( $value ) . '" ' . selected( $selected_state, $value, false ) . '>' . esc_html( $label ) . '</option>';
    }
    echo '</select>';
}

add_action( 'pre_get_posts', 'ffl_admin_apply_filters' );
function ffl_admin_apply_filters( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() || 'ffl_einsatz' !== $query->get( 'post_type' ) ) {
        return;
    }

    $orderby = $query->get( 'orderby' );
    if ( 'ffl_alarm' === $orderby ) {
        $query->set( 'meta_key', '_ffl_alarmzeit' );
        $query->set( 'orderby', 'meta_value' );
    } elseif ( 'ffl_number' === $orderby ) {
        $query->set( 'meta_key', '_ffl_manuelle_einsatznummer' );
        $query->set( 'orderby', 'meta_value_num' );
    } elseif ( 'ffl_location' === $orderby ) {
        $query->set( 'meta_key', '_ffl_einsatzort' );
        $query->set( 'orderby', 'meta_value' );
    }

    $meta_query = (array) $query->get( 'meta_query' );
    if ( ! empty( $_GET['ffl_year'] ) ) {
        $year = absint( $_GET['ffl_year'] );
        if ( $year >= 1900 && $year <= 2200 ) {
            $meta_query[] = array(
                'key'     => '_ffl_alarmzeit',
                'value'   => sprintf( '%04d-', $year ),
                'compare' => 'LIKE',
            );
        }
    }
    if ( $meta_query ) {
        $query->set( 'meta_query', $meta_query );
    }

    if ( ! empty( $_GET['ffl_einsatzart_filter'] ) ) {
        $slug = sanitize_title( wp_unslash( $_GET['ffl_einsatzart_filter'] ) );
        $query->set(
            'tax_query',
            array(
                array(
                    'taxonomy' => 'ffl_einsatzart',
                    'field'    => 'slug',
                    'terms'    => $slug,
                ),
            )
        );
    }

    $state = isset( $_GET['ffl_completeness'] ) ? sanitize_key( $_GET['ffl_completeness'] ) : '';
    if ( $state ) {
        global $wpdb;
        $ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status NOT IN ('trash','auto-draft')", 'ffl_einsatz' ) );
        $matches = array();
        foreach ( array_map( 'absint', $ids ) as $post_id ) {
            $gallery = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $post_id, '_ffl_gallery_ids', true ) ) ) );
            $featured = has_post_thumbnail( $post_id );
            $coords = '' !== trim( (string) get_post_meta( $post_id, '_ffl_lat', true ) ) && '' !== trim( (string) get_post_meta( $post_id, '_ffl_lon', true ) );
            $missing = ffl_admin_missing_fields( $post_id );
            $ok = false;
            if ( 'incomplete' === $state ) {
                $ok = ! empty( $missing );
            } elseif ( 'complete' === $state ) {
                $ok = empty( $missing );
            } elseif ( 'with_image' === $state ) {
                $ok = $featured || ! empty( $gallery );
            } elseif ( 'missing_image' === $state ) {
                $ok = ! $featured && ! $gallery;
            } elseif ( 'missing_featured' === $state ) {
                $ok = ! $featured;
            } elseif ( 'with_gallery' === $state ) {
                $ok = ! empty( $gallery );
            } elseif ( 'missing_gallery' === $state ) {
                $ok = empty( $gallery );
            } elseif ( 'with_coords' === $state ) {
                $ok = $coords;
            } elseif ( 'missing_coords' === $state ) {
                $ok = ! $coords;
            }
            if ( $ok ) {
                $matches[] = $post_id;
            }
        }
        $query->set( 'post__in', $matches ? $matches : array( 0 ) );
    }
}

/** Backend-Suche zusätzlich auf Einsatzort, Alarmstichwort und Nummer erweitern. */
add_filter( 'posts_search', 'ffl_admin_extend_search', 20, 2 );
function ffl_admin_extend_search( $search, $query ) {
    if ( ! is_admin() || ! $query->is_main_query() || 'ffl_einsatz' !== $query->get( 'post_type' ) || ! $query->get( 's' ) ) {
        return $search;
    }
    global $wpdb;
    $term = '%' . $wpdb->esc_like( $query->get( 's' ) ) . '%';
    $search = $wpdb->prepare(
        " AND ( {$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_content LIKE %s OR {$wpdb->posts}.post_excerpt LIKE %s OR EXISTS ( SELECT 1 FROM {$wpdb->postmeta} fflpm WHERE fflpm.post_id = {$wpdb->posts}.ID AND fflpm.meta_key IN ('_ffl_einsatzort','_ffl_alarmstichwort','_ffl_manuelle_einsatznummer') AND fflpm.meta_value LIKE %s ) ) ",
        $term,
        $term,
        $term,
        $term
    );
    return $search;
}

add_action( 'all_admin_notices', 'ffl_admin_overview_cards' );
function ffl_admin_overview_cards() {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen || 'edit-ffl_einsatz' !== $screen->id ) {
        return;
    }

    global $wpdb;
    $ids = array_map( 'absint', $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status NOT IN ('trash','auto-draft')", 'ffl_einsatz' ) ) );
    $year = (int) wp_date( 'Y' );
    $current_year = 0;
    $with_images = 0;
    $missing_featured = 0;
    $missing_coords = 0;
    $incomplete = 0;

    foreach ( $ids as $post_id ) {
        $alarm = (string) get_post_meta( $post_id, '_ffl_alarmzeit', true );
        if ( 0 === strpos( $alarm, (string) $year ) ) {
            $current_year++;
        }
        $gallery = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $post_id, '_ffl_gallery_ids', true ) ) ) );
        if ( has_post_thumbnail( $post_id ) || $gallery ) {
            $with_images++;
        }
        if ( ! has_post_thumbnail( $post_id ) ) {
            $missing_featured++;
        }
        if ( '' === trim( (string) get_post_meta( $post_id, '_ffl_lat', true ) ) || '' === trim( (string) get_post_meta( $post_id, '_ffl_lon', true ) ) ) {
            $missing_coords++;
        }
        if ( ffl_admin_missing_fields( $post_id ) ) {
            $incomplete++;
        }
    }

    $base = admin_url( 'edit.php?post_type=ffl_einsatz' );
    $cards = array(
        array( 'label' => ffl_lang( 'Einsätze insgesamt', 'Total Incidents' ), 'value' => count( $ids ), 'icon' => 'dashicons-clipboard', 'url' => $base ),
        array( 'label' => ffl_lang( 'Einsätze ', 'Incidents ' ) . $year, 'value' => $current_year, 'icon' => 'dashicons-calendar-alt', 'url' => add_query_arg( 'ffl_year', $year, $base ) ),
        array( 'label' => ffl_lang( 'Mit Einsatzbild', 'With Incident Image' ), 'value' => $with_images, 'icon' => 'dashicons-format-image', 'url' => add_query_arg( 'ffl_completeness', 'with_image', $base ) ),
        array( 'label' => ffl_lang( 'Ohne Titelbild', 'Without Featured Image' ), 'value' => $missing_featured, 'icon' => 'dashicons-hidden', 'url' => add_query_arg( 'ffl_completeness', 'missing_featured', $base ) ),
        array( 'label' => ffl_lang( 'Ohne Koordinaten', 'Without Coordinates' ), 'value' => $missing_coords, 'icon' => 'dashicons-location-alt', 'url' => add_query_arg( 'ffl_completeness', 'missing_coords', $base ) ),
        array( 'label' => ffl_lang( 'Unvollständig', 'Incomplete' ), 'value' => $incomplete, 'icon' => 'dashicons-warning', 'url' => add_query_arg( 'ffl_completeness', 'incomplete', $base ) ),
    );

    echo '<div class="ffl-admin-overview" aria-label="Einsatzübersicht">';
    foreach ( $cards as $card ) {
        echo '<a class="ffl-admin-overview__card" href="' . esc_url( $card['url'] ) . '"><span class="dashicons ' . esc_attr( $card['icon'] ) . '"></span><strong>' . esc_html( $card['value'] ) . '</strong><small>' . esc_html( $card['label'] ) . '</small></a>';
    }
    echo '</div>';
}

/**
 * Hilfsfunktionen für Frontend und Templates.
 */
function ffl_get_report_raw( $post_id ) {
    $post = get_post( $post_id );
    if ( $post && trim( (string) $post->post_content ) !== '' ) {
        return $post->post_content;
    }
    return (string) get_post_meta( $post_id, '_ffl_einsatzbericht', true );
}

function ffl_get_report_html( $post_id ) {
    $report = ffl_get_report_raw( $post_id );
    if ( trim( $report ) === '' ) {
        return '';
    }
    return apply_filters( 'the_content', $report );
}

function ffl_get_summary( $post_id, $length = 34 ) {
    $summary = trim( (string) get_post_meta( $post_id, '_ffl_kurzfassung', true ) );
    if ( $summary !== '' ) {
        return $summary;
    }

    $excerpt = trim( (string) get_post_field( 'post_excerpt', $post_id ) );
    if ( $excerpt !== '' ) {
        return $excerpt;
    }

    return wp_trim_words( wp_strip_all_tags( ffl_get_report_raw( $post_id ) ), $length, ' …' );
}

function ffl_get_alarm_timestamp( $post_id ) {
    $alarm = get_post_meta( $post_id, '_ffl_alarmzeit', true );
    $parsed = $alarm ? strtotime( $alarm ) : false;
    return $parsed ?: ( get_post_timestamp( $post_id ) ?: time() );
}

function ffl_get_duration( $post_id ) {
    $start = get_post_meta( $post_id, '_ffl_alarmzeit', true );
    $end   = get_post_meta( $post_id, '_ffl_endezeit', true );
    if ( ! $start || ! $end ) {
        return '';
    }

    $start_timestamp = strtotime( $start );
    $end_timestamp   = strtotime( $end );
    if ( ! $start_timestamp || ! $end_timestamp ) {
        return '';
    }

    $seconds = max( 0, $end_timestamp - $start_timestamp );
    $hours   = floor( $seconds / HOUR_IN_SECONDS );
    $minutes = floor( ( $seconds % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS );

    $parts = array();
    if ( $hours > 0 ) {
        $parts[] = $hours . ffl_lang( ' Std.', ' hr' . ( 1 === $hours ? '' : 's' ) );
    }
    if ( $minutes > 0 || $hours === 0 ) {
        $parts[] = $minutes . ffl_lang( ' Min.', ' min' );
    }
    return implode( ' ', $parts );
}

function ffl_get_primary_term( $post_id ) {
    $terms = get_the_terms( $post_id, 'ffl_einsatzart' );
    return ( $terms && ! is_wp_error( $terms ) ) ? reset( $terms ) : null;
}

function ffl_term_style( $post_id ) {
    $term = ffl_get_primary_term( $post_id );
    $slug = $term ? sanitize_title( $term->slug ) : 'sonstiger-einsatz';
    $name = $term ? $term->name : 'Einsatz';

    $map = array(
        'brandeinsatz'               => array( 'key' => 'fire', 'color' => '#e23b3b', 'icon' => 'fire' ),
        'technische-hilfeleistung'   => array( 'key' => 'technical', 'color' => '#2374d8', 'icon' => 'tools' ),
        'fehlalarm'                  => array( 'key' => 'false-alarm', 'color' => '#d89216', 'icon' => 'warning' ),
        'uebungseinsatz'             => array( 'key' => 'exercise', 'color' => '#288b5f', 'icon' => 'exercise' ),
    );
    $style = $map[ $slug ] ?? array( 'key' => 'other', 'color' => '#596579', 'icon' => 'signal' );
    $style['slug'] = $slug;
    $style['name'] = $name;
    return $style;
}

function ffl_get_einsatz_number( $post_id ) {
    $manual = trim( (string) get_post_meta( $post_id, '_ffl_manuelle_einsatznummer', true ) );
    if ( $manual !== '' ) {
        return $manual;
    }

    $year = wp_date( 'Y', ffl_get_alarm_timestamp( $post_id ) );
    static $year_maps = array();

    if ( ! isset( $year_maps[ $year ] ) ) {
        $start_number = ( $year === wp_date( 'Y' ) ) ? max( 1, absint( get_option( 'ffl_startnummer_aktuelles_jahr', 1 ) ) ) : 1;
        $ids = get_posts(
            array(
                'post_type'      => 'ffl_einsatz',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'meta_key'       => '_ffl_alarmzeit',
                'orderby'        => array( 'meta_value' => 'ASC', 'ID' => 'ASC' ),
                'meta_query'     => array(
                    array(
                        'key'     => '_ffl_alarmzeit',
                        'value'   => $year,
                        'compare' => 'LIKE',
                    ),
                ),
                'no_found_rows'  => true,
            )
        );

        $year_maps[ $year ] = array();
        foreach ( array_values( $ids ) as $index => $id ) {
            $year_maps[ $year ][ (int) $id ] = (string) ( $start_number + $index );
        }
    }

    return $year_maps[ $year ][ (int) $post_id ] ?? '1';
}

function ffl_get_gallery_ids( $post_id ) {
    $ids = array();
    if ( has_post_thumbnail( $post_id ) ) {
        $ids[] = get_post_thumbnail_id( $post_id );
    }
    $gallery = get_post_meta( $post_id, '_ffl_gallery_ids', true );
    if ( $gallery ) {
        $ids = array_merge( $ids, array_filter( array_map( 'absint', explode( ',', $gallery ) ) ) );
    }
    return array_values( array_unique( array_filter( $ids ) ) );
}

function ffl_get_preview_image_id( $post_id ) {
    if ( has_post_thumbnail( $post_id ) ) {
        return (int) get_post_thumbnail_id( $post_id );
    }
    $gallery = ffl_get_gallery_ids( $post_id );
    return $gallery ? (int) reset( $gallery ) : 0;
}

/**
 * Liefert das eindeutige Vorschaubild eines Einsatzes für WhatsApp,
 * Facebook und andere Dienste. Reihenfolge: Beitragsbild, erstes
 * Galeriebild, neutrales Plugin-Fallback. Dadurch wird nie mehr das
 * allgemeine Startseitenbild als Einsatzvorschau verwendet.
 */
function ffl_get_social_image_data( $post_id ) {
    $post_id = absint( $post_id );
    $image_id = ffl_get_preview_image_id( $post_id );

    if ( $image_id ) {
        $src = wp_get_attachment_image_src( $image_id, 'full' );
        if ( is_array( $src ) && ! empty( $src[0] ) ) {
            $mime = (string) get_post_mime_type( $image_id );
            $alt  = trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) );
            if ( '' === $alt ) {
                $alt = get_the_title( $post_id );
            }

            return array(
                'id'     => $image_id,
                'url'    => esc_url_raw( $src[0] ),
                'width'  => absint( $src[1] ?? 0 ),
                'height' => absint( $src[2] ?? 0 ),
                'type'   => $mime ?: 'image/jpeg',
                'alt'    => $alt,
            );
        }
    }

    $default_image_id = absint( get_option( 'ffl_default_image_id', 0 ) );
    if ( $default_image_id ) {
        $src = wp_get_attachment_image_src( $default_image_id, 'full' );
        if ( is_array( $src ) && ! empty( $src[0] ) ) {
            $mime = (string) get_post_mime_type( $default_image_id );
            $alt  = trim( (string) get_post_meta( $default_image_id, '_wp_attachment_image_alt', true ) );
            if ( '' === $alt ) {
                $alt = 'Einsatzbericht ' . get_the_title( $post_id );
            }
            return array(
                'id'     => $default_image_id,
                'url'    => esc_url_raw( $src[0] ),
                'width'  => absint( $src[1] ?? 0 ),
                'height' => absint( $src[2] ?? 0 ),
                'type'   => $mime ?: 'image/jpeg',
                'alt'    => $alt,
            );
        }
    }

    return array(
        'id'     => 0,
        'url'    => FFL_EINSATZLYZER_URL . 'images/einsatzbericht.png',
        'width'  => 882,
        'height' => 859,
        'type'   => 'image/png',
        'alt'    => 'Einsatzbericht ' . get_the_title( $post_id ),
    );
}

function ffl_get_current_social_image_data() {
    if ( ! is_singular( 'ffl_einsatz' ) ) {
        return array();
    }
    return ffl_get_social_image_data( get_queried_object_id() );
}

/** Yoast SEO: Einsatzbild vor das globale Standardbild setzen. */
add_filter( 'wpseo_opengraph_image', 'ffl_filter_social_image_url', 99 );
add_filter( 'wpseo_twitter_image', 'ffl_filter_social_image_url', 99 );
function ffl_filter_social_image_url( $url ) {
    $data = ffl_get_current_social_image_data();
    return ! empty( $data['url'] ) ? $data['url'] : $url;
}

add_filter( 'wpseo_opengraph_image_width', 'ffl_filter_social_image_width', 99 );
function ffl_filter_social_image_width( $width ) {
    $data = ffl_get_current_social_image_data();
    return ! empty( $data['width'] ) ? $data['width'] : $width;
}

add_filter( 'wpseo_opengraph_image_height', 'ffl_filter_social_image_height', 99 );
function ffl_filter_social_image_height( $height ) {
    $data = ffl_get_current_social_image_data();
    return ! empty( $data['height'] ) ? $data['height'] : $height;
}

add_filter( 'wpseo_opengraph_image_type', 'ffl_filter_social_image_type', 99 );
function ffl_filter_social_image_type( $type ) {
    $data = ffl_get_current_social_image_data();
    if ( empty( $data['type'] ) ) {
        return $type;
    }
    return str_replace( 'image/', '', $data['type'] );
}

add_filter( 'wpseo_add_opengraph_images', 'ffl_add_yoast_social_image_first', 99 );
function ffl_add_yoast_social_image_first( $image_container ) {
    $data = ffl_get_current_social_image_data();
    if ( ! empty( $data['id'] ) && is_object( $image_container ) && method_exists( $image_container, 'add_image_by_id' ) ) {
        $image_container->add_image_by_id( $data['id'] );
    }
    return $image_container;
}

add_filter( 'wpseo_schema_main_image_id', 'ffl_filter_yoast_schema_main_image_id', 99 );
function ffl_filter_yoast_schema_main_image_id( $image_id ) {
    $data = ffl_get_current_social_image_data();
    return ! empty( $data['id'] ) ? $data['id'] : $image_id;
}

/**
 * Yoast SEO: Einsatzberichte sind redaktionelle Berichte und keine Events.
 * Dadurch fordert Google keine Veranstaltungsfelder wie offers, performer,
 * organizer oder endDate mehr an.
 */
add_filter( 'wpseo_schema_article_type', 'ffl_filter_yoast_article_type', 99 );
function ffl_filter_yoast_article_type( $type ) {
    return is_singular( 'ffl_einsatz' ) ? 'NewsArticle' : $type;
}

add_filter( 'wpseo_schema_breadcrumb', 'ffl_filter_yoast_breadcrumb_schema', 99 );
function ffl_filter_yoast_breadcrumb_schema( $data ) {
    if ( ! is_singular( 'ffl_einsatz' ) || ! is_array( $data ) ) {
        return $data;
    }

    $post_id   = get_queried_object_id();
    $permalink = get_permalink( $post_id );
    $title     = get_the_title( $post_id );

    /*
     * Google erwartet bei jedem Breadcrumb außer optional beim letzten Element
     * eine echte URL in `item`. Manche Yoast-/Theme-Kombinationen liefern dort
     * nur ein unvollständiges Thing-Objekt. Wir setzen deshalb bewusst einfache
     * URL-Strings und vollständige Namen/Positionen.
     */
    $data['@type'] = 'BreadcrumbList';
    $data['@id']   = trailingslashit( $permalink ) . '#breadcrumb';
    $data['itemListElement'] = array(
        array(
            '@type'    => 'ListItem',
            'position' => 1,
            'name'     => 'Startseite',
            'item'     => home_url( '/' ),
        ),
        array(
            '@type'    => 'ListItem',
            'position' => 2,
            'name'     => 'Feuerwehr-Einsätze',
            'item'     => ffl_get_archive_url(),
        ),
        array(
            '@type'    => 'ListItem',
            'position' => 3,
            'name'     => $title,
            'item'     => $permalink,
        ),
    );

    return $data;
}

add_filter( 'wpseo_schema_article', 'ffl_filter_yoast_article_schema', 99 );
function ffl_filter_yoast_article_schema( $data ) {
    if ( ! is_singular( 'ffl_einsatz' ) || ! is_array( $data ) ) {
        return $data;
    }

    $post_id  = get_queried_object_id();
    $location = ffl_meta_value( $post_id, '_ffl_einsatzort' );
    $style    = ffl_term_style( $post_id );
    $summary  = ffl_get_summary( $post_id, 36 );

    $data['@type']          = 'NewsArticle';
    $data['headline']       = get_the_title( $post_id );
    $data['datePublished']  = get_the_date( DATE_W3C, $post_id );
    $data['dateModified']   = get_the_modified_date( DATE_W3C, $post_id );
    $data['articleSection'] = $style['name'];

    if ( $summary ) {
        $data['description'] = $summary;
    }

    $social_image = ffl_get_social_image_data( $post_id );
    if ( ! empty( $social_image['url'] ) ) {
        $data['image'] = array(
            '@type'  => 'ImageObject',
            'url'    => $social_image['url'],
            'width'  => absint( $social_image['width'] ?? 0 ),
            'height' => absint( $social_image['height'] ?? 0 ),
        );
    }

    if ( $location ) {
        $data['contentLocation'] = array(
            '@type' => 'Place',
            'name'  => $location,
        );
    }

    // Alte oder fremd ergänzte Event-Angaben innerhalb des Artikelknotens entfernen.
    if ( isset( $data['about']['@type'] ) && 'Event' === $data['about']['@type'] ) {
        unset( $data['about'] );
    }

    return $data;
}

/** Rank Math: Einsatzbild statt globalem Standardbild verwenden. */
add_filter( 'rank_math/opengraph/facebook/image', 'ffl_filter_social_image_url', 99 );
add_filter( 'rank_math/opengraph/twitter/image', 'ffl_filter_social_image_url', 99 );

/** SEOPress: Einsatzbild statt globalem Standardbild verwenden. */
add_filter( 'seopress_social_og_thumb', 'ffl_filter_social_image_url', 99 );
add_filter( 'seopress_social_twitter_card_thumb', 'ffl_filter_social_image_url', 99 );

function ffl_get_visual_icon( $post_id ) {
    $style = ffl_term_style( $post_id );
    $text  = strtolower( get_the_title( $post_id ) . ' ' . ffl_meta_value( $post_id, '_ffl_alarmstichwort' ) );
    if ( preg_match( '/gefahrgut|chemie|gas|stoffaustritt|öl|oel/', $text ) ) {
        return 'hazard';
    }
    if ( preg_match( '/sturm|unwetter|baum|astbruch|wasser|hochwasser/', $text ) ) {
        return 'weather';
    }
    return $style['icon'];
}

function ffl_get_public_coordinates( $post_id ) {
    $privacy = ffl_meta_value( $post_id, '_ffl_location_privacy', 'exact' );
    $lat     = (float) get_post_meta( $post_id, '_ffl_lat', true );
    $lon     = (float) get_post_meta( $post_id, '_ffl_lon', true );

    if ( $privacy === 'hidden' || ! $lat || ! $lon ) {
        return null;
    }

    if ( $privacy === 'approx' ) {
        $angle  = deg2rad( $post_id % 360 );
        $radius = 0.0035;
        $lat   += cos( $angle ) * $radius;
        $lon   += sin( $angle ) * $radius;
    }

    return array( 'lat' => $lat, 'lon' => $lon, 'privacy' => $privacy );
}

function ffl_haversine_distance_km( $lat1, $lon1, $lat2, $lon2 ) {
    $earth_radius = 6371.0088;
    $lat1         = deg2rad( (float) $lat1 );
    $lat2         = deg2rad( (float) $lat2 );
    $delta_lat    = $lat2 - $lat1;
    $delta_lon    = deg2rad( (float) $lon2 - (float) $lon1 );
    $a            = sin( $delta_lat / 2 ) ** 2 + cos( $lat1 ) * cos( $lat2 ) * sin( $delta_lon / 2 ) ** 2;
    return $earth_radius * 2 * atan2( sqrt( $a ), sqrt( max( 0, 1 - $a ) ) );
}

function ffl_get_einsatz_distance( $post_id, $coords = null ) {
    $mode = ffl_get_distance_mode();
    if ( 'none' === $mode ) {
        return null;
    }

    if ( ! $coords ) {
        $coords = ffl_get_public_coordinates( $post_id );
    }
    $station = ffl_get_station_coordinates();
    if ( ! $coords || ! $station ) {
        return null;
    }

    $result = array(
        'distance_km' => round( ffl_haversine_distance_km( $station['lat'], $station['lon'], $coords['lat'], $coords['lon'] ), 1 ),
        'duration'    => '',
        'source'      => 'air',
    );

    $result['approx']       = 'approx' === ( $coords['privacy'] ?? 'exact' );
    $result['station_name'] = $station['name'];
    $prefix                 = $result['approx'] ? 'ca. ' : '';
    $result['label']        = $prefix . number_format_i18n( $result['distance_km'], 1 ) . ' km Luftlinie';

    return $result;
}

function ffl_get_osm_route_url( $coords ) {
    $station = ffl_get_station_coordinates();
    if ( ! $station || ! is_array( $coords ) ) {
        return '';
    }

    $route = implode(
        ';',
        array(
            (float) $station['lat'] . ',' . (float) $station['lon'],
            (float) $coords['lat'] . ',' . (float) $coords['lon'],
        )
    );

    return add_query_arg(
        array(
            'engine' => 'fossgis_osrm_car',
            'route'  => $route,
        ),
        'https://www.openstreetmap.org/directions'
    );
}

function ffl_get_external_map_url( $coords, $title = '' ) {
    if ( ! is_array( $coords ) ) {
        return '';
    }

    $lat   = (string) $coords['lat'];
    $lon   = (string) $coords['lon'];
    $title = trim( (string) $title ) ?: 'Einsatzort';

    return 'https://www.openstreetmap.org/?mlat=' . rawurlencode( $lat ) . '&mlon=' . rawurlencode( $lon ) . '#map=15/' . rawurlencode( $lat ) . '/' . rawurlencode( $lon );
}

function ffl_parse_list( $value ) {
    $items = preg_split( '/[\r\n,;]+/', (string) $value );
    return array_values( array_filter( array_map( 'trim', $items ) ) );
}

function ffl_parse_timeline( $value ) {
    $events = array();
    foreach ( preg_split( '/\r\n|\r|\n/', (string) $value ) as $line ) {
        $line = trim( $line );
        if ( $line === '' ) {
            continue;
        }
        $parts = array_map( 'trim', explode( '|', $line, 2 ) );
        $events[] = array(
            'time'  => $parts[0] ?? '',
            'event' => $parts[1] ?? $parts[0],
        );
    }
    return $events;
}

function ffl_icon( $name ) {
    $icons = array(
        'clock' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm1 11h5v-2h-4V6h-2v7Z"/></svg>',
        'pin' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 14.5 9 2.5 2.5 0 0 1 12 11.5Z"/></svg>',
        'signal' => '<svg viewBox="0 0 64 64" aria-hidden="true"><path d="M23 45h18l-2.4-18.1A6.7 6.7 0 0 0 32 21a6.7 6.7 0 0 0-6.6 5.9L23 45Zm-5 0h28v6H18v-6Zm12-34h4v7h-4v-7ZM11.5 19.3l2.8-2.8 5 5-2.8 2.8-5-5Zm38.2-2.8 2.8 2.8-5 5-2.8-2.8 5-5ZM6 31h9v4H6v-4Zm43 0h9v4h-9v-4Z"/></svg>',
        'fire' => '<svg viewBox="0 0 64 64" aria-hidden="true"><path d="M35.7 5.5c2.1 9-5.8 12.7-3.1 20.1 1.4 3.8 5.5 5.4 8.7 2.7 2.1-1.8 2.6-4.8 2.1-7.5 8.7 7.1 11.5 17.8 6.5 27.1C46.1 55 39.4 59 31.7 59 20.3 59 11 50.2 11 39.3c0-8.4 4.9-15.6 12-20.3-.5 6 1.4 10.1 5.1 10.5 6.3.7 2.1-14.2 7.6-24Z"/><path d="M34 34c.8 4.2-2.6 6-1.1 9.4 1.1 2.5 4.1 2.6 5.8.5 3.5 5.1.6 11.6-6.5 11.6-5.8 0-10.2-4.2-10.2-9.6 0-4 2.4-7.6 6-9.7-.1 3.6 1.2 5.7 3.4 5.5 3-.2.5-5.7 2.6-7.7Z" opacity=".55"/></svg>',
        'tools' => '<svg viewBox="0 0 64 64" aria-hidden="true"><path d="M39.8 8.2a15 15 0 0 0-18.4 18.4L7.7 40.3a7.8 7.8 0 0 0 11 11l13.7-13.7A15 15 0 0 0 50.8 19l-9.1 9.1-6.9-1.8-1.8-6.9 9.1-9.1c-.7-.8-1.5-1.5-2.3-2.1ZM14.2 47.8a2.7 2.7 0 1 1 0-5.4 2.7 2.7 0 0 1 0 5.4Z"/></svg>',
        'warning' => '<svg viewBox="0 0 64 64" aria-hidden="true"><path d="M28.4 8.2a4.2 4.2 0 0 1 7.2 0l24 41.5A4.2 4.2 0 0 1 56 56H8a4.2 4.2 0 0 1-3.6-6.3l24-41.5ZM29 22v18h6V22h-6Zm3 27a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>',
        'exercise' => '<svg viewBox="0 0 64 64" aria-hidden="true"><path d="M32 4 8 13v17c0 14.9 10.2 25.5 24 30 13.8-4.5 24-15.1 24-30V13L32 4Zm0 11 6 11 12 2-9 8 2 12-11-5.5L21 48l2-12-9-8 12-2 6-11Z"/></svg>',
        'weather' => '<svg viewBox="0 0 64 64" aria-hidden="true"><path d="M12 20h29a7 7 0 1 0-6.5-9.6l-5.2-2A12 12 0 1 1 41 25H12v-5Zm0 10h39a8 8 0 1 1-7.2 11.5l5-2.3A3 3 0 1 0 51 35H12v-5Zm0 12h23a7 7 0 1 1-6.5 9.6l5.2-2A2 2 0 1 0 35 47H12v-5Z"/></svg>',
        'hazard' => '<svg viewBox="0 0 64 64" aria-hidden="true"><path d="m32 4 28 28-28 28L4 32 32 4Zm0 14a14 14 0 1 0 0 28 14 14 0 0 0 0-28Zm0 5 3 6 7 .9-5 4.8 1.2 6.8-6.2-3.2-6.2 3.2 1.2-6.8-5-4.8 7-.9 3-6Z"/></svg>',
        'camera' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 4 7.5 6H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-3.5L15 4Zm3 13a4.5 4.5 0 1 1 4.5-4.5A4.5 4.5 0 0 1 12 17Z"/></svg>',
        'share' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 16a3 3 0 0 0-2.39 1.19l-7-4.08a3.33 3.33 0 0 0 0-2.22l7-4.08A3 3 0 1 0 15 5a2.77 2.77 0 0 0 .09.68l-7 4.08a3 3 0 1 0 0 4.48l7 4.08A2.77 2.77 0 0 0 15 19a3 3 0 1 0 3-3Z"/></svg>',
        'arrow' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m13 5-1.4 1.4 4.6 4.6H4v2h12.2l-4.6 4.6L13 19l7-7Z"/></svg>',
        'search' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21 19.6-5.3-5.3a7.5 7.5 0 1 0-1.4 1.4l5.3 5.3ZM5 10a5 5 0 1 1 5 5 5 5 0 0 1-5-5Z"/></svg>',
    );
    return $icons[ $name ] ?? '';
}

/**
 * Shortcode und Archiv-Ausgabe.
 */
add_shortcode( 'ffl_einsatz_liste_komplett', 'ffl_render_einsatz_dashboard_shortcode' );
function ffl_render_einsatz_dashboard_shortcode() {
    ob_start();
    ffl_render_archive_content( true );
    return ob_get_clean();
}

function ffl_get_available_years() {
    global $wpdb;
    $sql = $wpdb->prepare(
        "SELECT DISTINCT LEFT(pm.meta_value, 4) AS year
         FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status = 'publish'
         ORDER BY year DESC",
        '_ffl_alarmzeit',
        'ffl_einsatz'
    );
    return array_filter( array_map( 'absint', $wpdb->get_col( $sql ) ) );
}

function ffl_get_archive_query_args( $is_shortcode = false, $request = null ) {
    $source = is_array( $request ) ? $request : $_GET;
    $year   = isset( $source['einsatz_jahr'] ) ? absint( $source['einsatz_jahr'] ) : 0;
    $type   = isset( $source['einsatz_art'] ) ? sanitize_title( wp_unslash( $source['einsatz_art'] ) ) : '';
    $term   = isset( $source['einsatz_suche'] ) ? sanitize_text_field( wp_unslash( $source['einsatz_suche'] ) ) : '';
    $page   = $is_shortcode ? max( 1, absint( $source['einsatzseite'] ?? 1 ) ) : max( 1, absint( $source['einsatzseite'] ?? get_query_var( 'paged' ) ) );

    $args = array(
        'post_type'      => 'ffl_einsatz',
        'post_status'    => 'publish',
        'posts_per_page' => 12,
        'paged'          => $page,
        'meta_key'       => '_ffl_alarmzeit',
        'orderby'        => 'meta_value',
        'order'          => 'DESC',
    );

    if ( $year ) {
        $args['meta_query'] = array(
            array(
                'key'     => '_ffl_alarmzeit',
                'value'   => (string) $year,
                'compare' => 'LIKE',
            ),
        );
    }
    if ( $type ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'ffl_einsatzart',
                'field'    => 'slug',
                'terms'    => $type,
            ),
        );
    }
    if ( $term ) {
        $args['ffl_search_term'] = $term;
    }

    return $args;
}

add_filter( 'posts_search', 'ffl_extend_einsatz_search', 10, 2 );
function ffl_extend_einsatz_search( $search_sql, $query ) {
    $term = $query->get( 'ffl_search_term' );
    if ( ! $term || $query->get( 'post_type' ) !== 'ffl_einsatz' ) {
        return $search_sql;
    }

    global $wpdb;
    $like = '%' . $wpdb->esc_like( $term ) . '%';

    return $wpdb->prepare(
        " AND (
            {$wpdb->posts}.post_title LIKE %s
            OR {$wpdb->posts}.post_content LIKE %s
            OR {$wpdb->posts}.post_excerpt LIKE %s
            OR EXISTS (
                SELECT 1 FROM {$wpdb->postmeta} ffl_search_meta
                WHERE ffl_search_meta.post_id = {$wpdb->posts}.ID
                AND ffl_search_meta.meta_key IN ('_ffl_einsatzort', '_ffl_alarmstichwort', '_ffl_kurzfassung', '_ffl_einsatzbericht')
                AND ffl_search_meta.meta_value LIKE %s
            )
        ) ",
        $like,
        $like,
        $like,
        $like
    );
}

function ffl_get_archive_state( $source = null ) {
    $source = is_array( $source ) ? $source : $_GET;
    return array(
        'year'   => absint( $source['einsatz_jahr'] ?? 0 ),
        'type'   => sanitize_title( wp_unslash( $source['einsatz_art'] ?? '' ) ),
        'search' => sanitize_text_field( wp_unslash( $source['einsatz_suche'] ?? '' ) ),
        'page'   => max( 1, absint( $source['einsatzseite'] ?? 1 ) ),
    );
}

function ffl_render_active_filters( $state ) {
    $chips = array();
    if ( $state['year'] ) {
        $chips[] = array( 'key' => 'einsatz_jahr', 'label' => ffl_lang( 'Jahr ', 'Year ' ) . $state['year'] );
    }
    if ( $state['type'] ) {
        $term = get_term_by( 'slug', $state['type'], 'ffl_einsatzart' );
        $chips[] = array( 'key' => 'einsatz_art', 'label' => $term && ! is_wp_error( $term ) ? ffl_term_display_name( $term->name ) : $state['type'] );
    }
    if ( $state['search'] ) {
        $chips[] = array( 'key' => 'einsatz_suche', 'label' => ffl_lang( 'Suche: ', 'Search: ' ) . $state['search'] );
    }
    if ( ! $chips ) {
        return;
    }
    echo '<div class="ffl-active-filters" aria-label="' . esc_attr( ffl_lang( 'Aktive Filter', 'Active Filters' ) ) . '">';
    foreach ( $chips as $chip ) {
        echo '<button type="button" data-clear-filter="' . esc_attr( $chip['key'] ) . '"><span>' . esc_html( $chip['label'] ) . '</span><b aria-hidden="true">×</b><span class="screen-reader-text">' . esc_html( ffl_lang( 'Filter entfernen', 'Remove Filter' ) ) . '</span></button>';
    }
    echo '<button type="button" class="ffl-active-filters__clear" data-clear-all>' . esc_html( ffl_lang( 'Alle Filter löschen', 'Clear All Filters' ) ) . '</button></div>';
}

function ffl_render_archive_results( $query, $is_shortcode, $state, $archive_url, $query_args ) {
    $map_data = ffl_get_overview_map_data( $query_args );
    ?>
    <div class="ffl-archive-results" data-archive-results aria-live="polite" aria-busy="false">
        <?php ffl_render_active_filters( $state ); ?>
        <?php if ( $query->have_posts() ) : ?>
            <div class="ffl-results-head">
                <div class="ffl-results-head__title"><span><?php echo esc_html( $query->found_posts ); ?> <?php echo esc_html( ffl_lang( 'Ergebnisse', 'Results' ) ); ?></span><h2><?php echo $state['year'] ? esc_html( ffl_lang( 'Einsätze ', 'Incidents ' ) . $state['year'] ) : esc_html( ffl_lang( 'Aktuelle Einsatzberichte', 'Latest Incident Reports' ) ); ?></h2></div>
                <?php if ( ! empty( $map_data['points'] ) ) :
                    $map_point_count = count( $map_data['points'] );
                    $map_subtitle = 1 === $map_point_count ? sprintf( ffl_lang( '%d Einsatzort auf der Karte entdecken', 'Discover %d incident location on the map' ), $map_point_count ) : sprintf( ffl_lang( '%d Einsatzorte auf der Karte entdecken', 'Discover %d incident locations on the map' ), $map_point_count );
                    ?>
                    <button type="button" class="ffl-map-toggle" data-map-toggle aria-expanded="false" aria-label="<?php echo esc_attr( ffl_lang( 'Einsatzorte auf Karte anzeigen', 'Show Incident Locations on Map' ) ); ?>" data-map-closed-subtitle="<?php echo esc_attr( $map_subtitle ); ?>">
                        <span class="ffl-map-toggle__icon" aria-hidden="true"><?php echo ffl_icon( 'pin' ); ?></span>
                        <span class="ffl-map-toggle__copy">
                            <strong data-map-toggle-label><?php echo esc_html( ffl_lang( 'Einsatzorte auf Karte anzeigen', 'Show Incident Locations on Map' ) ); ?></strong>
                            <small data-map-toggle-subtitle><?php echo esc_html( $map_subtitle ); ?></small>
                        </span>
                    </button>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $map_data['points'] ) ) : ?>
                <section class="ffl-overview-map-wrap" data-overview-wrap hidden aria-label="<?php echo esc_attr( ffl_lang( 'Karte der gefilterten Einsatzorte', 'Map of Filtered Incident Locations' ) ); ?>">
                    <div class="ffl-overview-map" data-overview-map tabindex="0" aria-label="<?php echo esc_attr( ffl_lang( 'Interaktive Einsatzkarte', 'Interactive Incident Map' ) ); ?>"></div>
                    <script type="application/json" class="ffl-overview-map-data"><?php echo wp_json_encode( $map_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?></script>
                </section>
            <?php endif; ?>

            <div class="ffl-card-grid">
                <?php while ( $query->have_posts() ) : $query->the_post(); ffl_render_einsatz_card( get_the_ID() ); endwhile; ?>
            </div>

            <?php
            $pagination_args = array(
                'total'      => $query->max_num_pages,
                'current'    => max( 1, $state['page'] ),
                'type'       => 'list',
                'prev_text'  => ffl_lang( '← Zurück', '← Previous' ),
                'next_text'  => ffl_lang( 'Weiter →', 'Next →' ),
                'add_args'   => array_filter( array( 'einsatz_jahr' => $state['year'] ?: null, 'einsatz_art' => $state['type'] ?: null, 'einsatz_suche' => $state['search'] ?: null ) ),
            );
            if ( $is_shortcode ) {
                $pagination_args['base']   = add_query_arg( 'einsatzseite', '%#%', $archive_url );
                $pagination_args['format'] = '';
            }
            $pagination = paginate_links( $pagination_args );
            if ( $pagination ) {
                echo '<nav class="ffl-pagination" aria-label="' . esc_attr( ffl_lang( 'Seitennavigation', 'Page Navigation' ) ) . '" data-nositemap="true">' . wp_kses_post( preg_replace( '/<a\s/i', '<a rel="nofollow" ', $pagination ) ) . '</nav>';
            }
            ?>
        <?php else : ?>
            <div class="ffl-empty-state"><span><?php echo ffl_icon( 'search' ); ?></span><h2><?php echo esc_html( ffl_lang( 'Keine Einsätze gefunden', 'No Incidents Found' ) ); ?></h2><p><?php echo esc_html( ffl_lang( 'Für diese Auswahl sind keine Einsatzberichte vorhanden.', 'No incident reports are available for this selection.' ) ); ?></p><button type="button" class="ffl-button ffl-button--primary" data-clear-all><?php echo esc_html( ffl_lang( 'Alle Einsätze anzeigen', 'Show All Incidents' ) ); ?></button></div>
        <?php endif; ?>

        <footer class="ffl-archive-footer" aria-label="Einsatzlyzer Copyright">
            <p class="ffl-archive-footer__copyright">Copyright © <?php echo esc_html( wp_date( 'Y' ) ); ?> <a href="https://www.ralf-ebert-it.de" target="_blank" rel="noopener noreferrer">Ralf Ebert</a>.</p>
            <img class="ffl-archive-footer__logo" src="<?php echo esc_url( FFL_EINSATZLYZER_URL . 'images/branding/einsatzlyzer-footer-logo.png' ); ?>" alt="Einsatzlyzer">
        </footer>
    </div>
    <?php
}

function ffl_render_archive_content( $is_shortcode = false ) {
    $state          = ffl_get_archive_state();
    $query_args     = ffl_get_archive_query_args( $is_shortcode );
    $state['page']   = max( 1, (int) ( $query_args['paged'] ?? 1 ) );
    $query          = new WP_Query( $query_args );
    $years          = ffl_get_available_years();
    $types          = get_terms( array( 'taxonomy' => 'ffl_einsatzart', 'hide_empty' => true ) );
    $count_object   = wp_count_posts( 'ffl_einsatz' );
    $total          = isset( $count_object->publish ) ? (int) $count_object->publish : 0;
    $current_year   = (int) wp_date( 'Y' );
    $current_query  = new WP_Query(
        array(
            'post_type'      => 'ffl_einsatz',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => '_ffl_alarmzeit',
                    'value'   => (string) $current_year,
                    'compare' => 'LIKE',
                ),
            ),
        )
    );
    $current_count = (int) $current_query->found_posts;
    $latest        = get_posts( array( 'post_type' => 'ffl_einsatz', 'post_status' => 'publish', 'posts_per_page' => 1, 'meta_key' => '_ffl_alarmzeit', 'orderby' => 'meta_value', 'order' => 'DESC' ) );
    $latest_date   = $latest ? wp_date( 'd.m.Y', ffl_get_alarm_timestamp( $latest[0]->ID ) ) : '—';
    $archive_url   = $is_shortcode ? get_permalink() : ffl_get_archive_url();
    ?>
    <div class="ffl-archive<?php echo $is_shortcode ? ' ffl-archive--shortcode' : ''; ?>" data-live-archive data-shortcode="<?php echo $is_shortcode ? '1' : '0'; ?>" data-archive-url="<?php echo esc_url( $archive_url ); ?>">
        <section class="ffl-archive-hero">
            <div class="ffl-archive-hero__glow"></div>
            <div class="ffl-shell ffl-archive-hero__inner">
                <div class="ffl-archive-hero__copy">
                    <span class="ffl-eyebrow ffl-archive-brand"><span><?php echo esc_html( ffl_lang( 'Einsatzarchiv', 'Incident Archive' ) ); ?></span></span>
                    <?php if ( $is_shortcode ) : ?>
                        <h2 class="ffl-archive-title"><?php echo esc_html( ffl_lang( 'Unsere Einsätze.', 'Our Incidents.' ) ); ?><br><span><?php echo esc_html( ffl_lang( 'Transparent dokumentiert.', 'Transparently Documented.' ) ); ?></span></h2>
                    <?php else : ?>
                        <h1 class="ffl-archive-title"><?php echo esc_html( ffl_lang( 'Unsere Einsätze.', 'Our Incidents.' ) ); ?><br><span><?php echo esc_html( ffl_lang( 'Transparent dokumentiert.', 'Transparently Documented.' ) ); ?></span></h1>
                    <?php endif; ?>
                    <?php $intro = get_option( 'ffl_archive_intro', '' ); if ( '' === trim( (string) $intro ) || 'Unsere Einsätze im Überblick – transparent, übersichtlich und mit allen wichtigen Informationen.' === $intro ) { $intro = ffl_lang( 'Unsere Einsätze im Überblick – transparent, übersichtlich und mit allen wichtigen Informationen.', 'Our incidents at a glance – transparent, clearly arranged and including all important information.' ); } ?><p><?php echo esc_html( $intro ); ?></p>
                </div>
                <div class="ffl-archive-stats" aria-label="<?php echo esc_attr( ffl_lang( 'Einsatzstatistik', 'Incident Statistics' ) ); ?>">
                    <div class="ffl-archive-stat"><strong><?php echo esc_html( $current_count ); ?></strong><span><?php echo esc_html( ffl_lang( 'Einsätze ', 'Incidents ') . $current_year ); ?></span></div>
                    <div class="ffl-archive-stat"><strong><?php echo esc_html( $total ); ?></strong><span><?php echo esc_html( ffl_lang( 'Insgesamt dokumentiert', 'Documented in Total' ) ); ?></span></div>
                    <div class="ffl-archive-stat ffl-archive-stat--latest"><strong><?php echo esc_html( $latest_date ); ?></strong><span><?php echo esc_html( ffl_lang( 'Letzter Einsatz', 'Latest Incident' ) ); ?></span></div>
                </div>
            </div>
        </section>

        <div class="ffl-shell ffl-archive-body">
            <section class="ffl-filter-panel" aria-label="<?php echo esc_attr( ffl_lang( 'Einsätze filtern', 'Filter Incidents' ) ); ?>">
                <form method="get" action="<?php echo esc_url( $archive_url ); ?>" class="ffl-filter-form" data-live-filter>
                    <label class="ffl-filter-search"><span><?php echo esc_html( ffl_lang( 'Suche', 'Search' ) ); ?></span><input type="search" name="einsatz_suche" value="<?php echo esc_attr( $state['search'] ); ?>" placeholder="<?php echo esc_attr( ffl_lang( 'Stichwort oder Ort suchen', 'Search keyword or location' ) ); ?>" autocomplete="off"></label>
                    <label><span><?php echo esc_html( ffl_lang( 'Jahr', 'Year' ) ); ?></span><select name="einsatz_jahr"><option value=""><?php echo esc_html( ffl_lang( 'Alle Jahre', 'All Years' ) ); ?></option><?php foreach ( $years as $year ) : ?><option value="<?php echo esc_attr( $year ); ?>" <?php selected( $state['year'], $year ); ?>><?php echo esc_html( $year ); ?></option><?php endforeach; ?></select></label>
                    <label><span><?php echo esc_html( ffl_lang( 'Einsatzart', 'Incident Type' ) ); ?></span><select name="einsatz_art"><option value=""><?php echo esc_html( ffl_lang( 'Alle Einsatzarten', 'All Incident Types' ) ); ?></option><?php if ( ! is_wp_error( $types ) ) : foreach ( $types as $type ) : ?><option value="<?php echo esc_attr( $type->slug ); ?>" <?php selected( $state['type'], $type->slug ); ?>><?php echo esc_html( ffl_term_display_name( $type->name ) ); ?></option><?php endforeach; endif; ?></select></label>
                    <button type="submit" class="ffl-button ffl-button--primary"><?php echo esc_html( ffl_lang( 'Filtern', 'Filter' ) ); ?></button>
                </form>
                <p class="ffl-filter-hint"><?php echo esc_html( ffl_lang( 'Die Ergebnisliste und die Karte aktualisieren sich automatisch.', 'The results list and map update automatically.' ) ); ?></p>
            </section>
            <?php ffl_render_archive_results( $query, $is_shortcode, $state, $archive_url, $query_args ); ?>
        </div>
    </div>
    <?php
    wp_reset_postdata();
}

add_action( 'wp_ajax_ffl_filter_archive', 'ffl_ajax_filter_archive' );
add_action( 'wp_ajax_nopriv_ffl_filter_archive', 'ffl_ajax_filter_archive' );
function ffl_ajax_filter_archive() {
    check_ajax_referer( 'ffl_filter_archive', 'nonce' );
    $is_shortcode = ! empty( $_POST['is_shortcode'] );
    $state        = ffl_get_archive_state( $_POST );
    $query_args   = ffl_get_archive_query_args( $is_shortcode, $_POST );
    $query        = new WP_Query( $query_args );
    $archive_url  = isset( $_POST['archive_url'] ) ? esc_url_raw( wp_unslash( $_POST['archive_url'] ) ) : ffl_get_archive_url();
    ob_start();
    ffl_render_archive_results( $query, $is_shortcode, $state, $archive_url, $query_args );
    $html = ob_get_clean();
    wp_reset_postdata();
    wp_send_json_success( array( 'html' => $html, 'count' => (int) $query->found_posts ) );
}

function ffl_render_einsatz_card( $post_id ) {
    $style       = ffl_term_style( $post_id );
    $timestamp   = ffl_get_alarm_timestamp( $post_id );
    $location    = ffl_meta_value( $post_id, '_ffl_einsatzort', ffl_lang( 'Einsatzort nicht veröffentlicht', 'Incident location not published' ) );
    $keyword     = ffl_meta_value( $post_id, '_ffl_alarmstichwort' );
    $duration    = ffl_get_duration( $post_id );
    $gallery     = ffl_get_gallery_ids( $post_id );
    $preview_id  = ffl_get_preview_image_id( $post_id );
    $summary     = trim( ffl_get_summary( $post_id, 28 ) );
    $visual_icon = ffl_get_visual_icon( $post_id );
    ?>
    <article class="ffl-card ffl-card--<?php echo esc_attr( $style['key'] ); ?><?php echo $summary ? ' has-summary' : ' has-no-summary'; ?>" style="--ffl-accent:<?php echo esc_attr( $style['color'] ); ?>">
        <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="ffl-card__media" aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?> <?php echo esc_attr( ffl_lang( 'ansehen', 'view' ) ); ?>">
            <?php if ( $preview_id ) : ?>
                <?php echo wp_get_attachment_image( $preview_id, 'large', false, array( 'loading' => 'lazy', 'class' => 'ffl-card__image', 'sizes' => '(max-width: 780px) 100vw, 50vw' ) ); ?>
                <span class="ffl-card__image-shade" aria-hidden="true"></span>
            <?php else : ?>
                <div class="ffl-card__fallback" aria-hidden="true">
                    <span class="ffl-fallback-visual__grid"></span>
                    <span class="ffl-fallback-visual__rings"></span>
                    <span class="ffl-fallback-visual__signal ffl-fallback-visual__signal--<?php echo esc_attr( $visual_icon ); ?>"><?php echo ffl_icon( $visual_icon ); ?></span>
                    <span class="ffl-fallback-visual__label"><?php echo esc_html( ffl_lang( 'Einsatz', 'Incident' ) ); ?></span>
                    <span class="ffl-fallback-visual__number"><?php echo esc_html( ffl_get_einsatz_number( $post_id ) ); ?></span>
                </div>
            <?php endif; ?>
            <span class="ffl-card__type"><?php echo esc_html( ffl_term_display_name( $style['name'] ) ); ?></span>
            <?php if ( count( $gallery ) > 1 ) : ?><span class="ffl-card__photos"><?php echo ffl_icon( 'camera' ); ?> <?php echo esc_html( count( $gallery ) ); ?></span><?php endif; ?>
        </a>
        <div class="ffl-card__body">
            <div class="ffl-card__meta"><span><?php echo esc_html( ffl_archive_date( $timestamp ) ); ?></span><span><?php echo esc_html( ffl_lang( 'Einsatz ', 'Incident ') . ffl_get_einsatz_number( $post_id ) ); ?></span></div>
            <?php if ( $keyword ) : ?><div class="ffl-card__keyword"><?php echo esc_html( $keyword ); ?></div><?php endif; ?>
            <h2 class="ffl-card__title"><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h2>
            <?php if ( $summary ) : ?><p class="ffl-card__summary"><?php echo esc_html( $summary ); ?></p><?php endif; ?>
            <div class="ffl-card__facts"><span><?php echo ffl_icon( 'clock' ); ?> <?php echo esc_html( wp_date( 'H:i', $timestamp ) ); ?> <?php echo esc_html( ffl_lang( 'Uhr', '' ) ); ?><?php echo $duration ? ' · ' . esc_html( $duration ) : ''; ?></span><span><?php echo ffl_icon( 'pin' ); ?> <?php echo esc_html( $location ); ?></span></div>
            <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="ffl-card__link"><?php echo esc_html( ffl_lang( 'Einsatzbericht ansehen', 'View Incident Report' ) ); ?> <?php echo ffl_icon( 'arrow' ); ?></a>
        </div>
    </article>
    <?php
}

function ffl_get_overview_map_data( $query_args = array() ) {
    $args = wp_parse_args(
        $query_args,
        array(
            'post_type'   => 'ffl_einsatz',
            'post_status' => 'publish',
            'meta_key'    => '_ffl_alarmzeit',
            'orderby'     => 'meta_value',
            'order'       => 'DESC',
        )
    );
    $args['posts_per_page'] = -1;
    $args['paged']          = 1;
    $args['no_found_rows']  = true;
    unset( $args['fields'] );
    $posts = get_posts( $args );
    $points = array();
    foreach ( $posts as $post ) {
        $coords = ffl_get_public_coordinates( $post->ID );
        if ( ! $coords ) {
            continue;
        }
        $style = ffl_term_style( $post->ID );
        $points[] = array(
            'lat'      => $coords['lat'],
            'lon'      => $coords['lon'],
            'title'    => get_the_title( $post->ID ),
            'url'      => get_permalink( $post->ID ),
            'date'     => wp_date( 'd.m.Y', ffl_get_alarm_timestamp( $post->ID ) ),
            'location' => ffl_meta_value( $post->ID, '_ffl_einsatzort' ),
            'type'     => ffl_term_display_name( $style['name'] ),
            'color'    => $style['color'],
            'icon'     => ffl_get_visual_icon( $post->ID ),
        );
    }

    return array(
        'points' => $points,
    );
}

/**
 * SEO: indexierbare Seiten, Beschreibung und strukturierte Daten.
 */
add_filter( 'wp_robots', 'ffl_robots' );
function ffl_robots( $robots ) {
    if ( is_singular( 'ffl_einsatz' ) ) {
        $robots['index']  = true;
        $robots['follow'] = true;
        unset( $robots['noindex'], $robots['nofollow'] );
    }

    if ( is_post_type_archive( 'ffl_einsatz' ) ) {
        $filtered = ! empty( $_GET['einsatz_jahr'] ) || ! empty( $_GET['einsatz_art'] ) || ! empty( $_GET['einsatz_suche'] ) || absint( $_GET['einsatzseite'] ?? 1 ) > 1 || get_query_var( 'paged' ) > 1;
        if ( $filtered ) {
            $robots['noindex'] = true;
            $robots['follow']  = true;
            unset( $robots['index'] );
        } else {
            $robots['index']  = true;
            $robots['follow'] = true;
            unset( $robots['noindex'], $robots['nofollow'] );
        }
    }
    return $robots;
}

function ffl_has_known_seo_plugin() {
    return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

add_action( 'wp_head', 'ffl_output_seo_data', 5 );
function ffl_output_seo_data() {
    if ( ! is_singular( 'ffl_einsatz' ) ) {
        return;
    }

    $post_id   = get_queried_object_id();
    $summary   = ffl_get_summary( $post_id, 36 );
    $permalink = get_permalink( $post_id );
    $title     = get_the_title( $post_id );
    $social_image = ffl_get_social_image_data( $post_id );
    $image        = absint( $social_image['id'] ?? 0 );
    $image_url    = (string) ( $social_image['url'] ?? '' );

    if ( ! ffl_has_known_seo_plugin() ) {
        if ( $summary ) {
            echo '<meta name="description" content="' . esc_attr( $summary ) . '">' . "\n";
        }
        echo '<link rel="canonical" href="' . esc_url( $permalink ) . '">' . "\n";
        echo '<meta property="og:type" content="article">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url( $permalink ) . '">' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
        if ( $summary ) {
            echo '<meta property="og:description" content="' . esc_attr( $summary ) . '">' . "\n";
        }
        if ( $image_url ) {
            echo '<meta property="og:image" content="' . esc_url( $image_url ) . '">' . "\n";
            echo '<meta property="og:image:secure_url" content="' . esc_url( $image_url ) . '">' . "\n";
            if ( ! empty( $social_image['width'] ) ) {
                echo '<meta property="og:image:width" content="' . absint( $social_image['width'] ) . '">' . "\n";
            }
            if ( ! empty( $social_image['height'] ) ) {
                echo '<meta property="og:image:height" content="' . absint( $social_image['height'] ) . '">' . "\n";
            }
            if ( ! empty( $social_image['type'] ) ) {
                echo '<meta property="og:image:type" content="' . esc_attr( $social_image['type'] ) . '">' . "\n";
            }
            if ( ! empty( $social_image['alt'] ) ) {
                echo '<meta property="og:image:alt" content="' . esc_attr( $social_image['alt'] ) . '">' . "\n";
            }
            echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
            echo '<meta name="twitter:image" content="' . esc_url( $image_url ) . '">' . "\n";
        } else {
            echo '<meta name="twitter:card" content="summary">' . "\n";
        }
    }

    /*
     * Yoast erzeugt bereits ein zusammenhängendes Schema-Diagramm inklusive
     * NewsArticle und BreadcrumbList. Ein zweiter Knoten mit derselben
     * #breadcrumb-ID kann von Google zusammengeführt und dadurch unvollständig
     * ausgewertet werden. Bei aktivem Yoast übernimmt deshalb ausschließlich
     * Yoast die strukturierten Daten; unsere Filter oben ergänzen den Artikeltyp.
     */
    if ( defined( 'WPSEO_VERSION' ) ) {
        return;
    }

    $style     = ffl_term_style( $post_id );
    $location  = ffl_meta_value( $post_id, '_ffl_einsatzort' );
    $article   = array(
        '@type'            => 'NewsArticle',
        '@id'              => $permalink . '#einsatzbericht',
        'headline'         => $title,
        'description'      => $summary,
        'datePublished'    => get_the_date( DATE_W3C, $post_id ),
        'dateModified'     => get_the_modified_date( DATE_W3C, $post_id ),
        'mainEntityOfPage' => array( '@type' => 'WebPage', '@id' => $permalink ),
        'articleSection'   => $style['name'],
        'author'           => array(
            '@type' => 'Organization',
            'name'  => get_option( 'ffl_organisation_name', get_bloginfo( 'name' ) ),
            'url'   => home_url( '/' ),
        ),
        'publisher'        => array(
            '@type' => 'Organization',
            'name'  => get_option( 'ffl_organisation_name', get_bloginfo( 'name' ) ),
            'url'   => home_url( '/' ),
        ),
    );
    if ( $image_url ) {
        $article['image'] = array( $image_url );
    }
    if ( $location ) {
        $article['contentLocation'] = array( '@type' => 'Place', 'name' => $location );
    }

    $breadcrumb = array(
        '@type'           => 'BreadcrumbList',
        '@id'             => $permalink . '#einsatzlyzer-breadcrumb',
        'itemListElement' => array(
            array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Startseite', 'item' => array( '@id' => home_url( '/' ), 'name' => 'Startseite' ) ),
            array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Einsätze', 'item' => array( '@id' => ffl_get_archive_url(), 'name' => 'Einsätze' ) ),
            array( '@type' => 'ListItem', 'position' => 3, 'name' => $title, 'item' => array( '@id' => $permalink, 'name' => $title ) ),
        ),
    );

    $schema = array(
        '@context' => 'https://schema.org',
        '@graph'   => array( $article, $breadcrumb ),
    );
    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . '</script>' . "\n";
}

/**
 * Einsatz bequem duplizieren. Alle vorhandenen Felder, Kategorien und Bilder
 * werden übernommen; die Kopie wird bewusst als Entwurf angelegt.
 */
add_filter( 'post_row_actions', 'ffl_add_duplicate_row_action', 10, 2 );
function ffl_add_duplicate_row_action( $actions, $post ) {
    if ( $post->post_type !== 'ffl_einsatz' || ! current_user_can( 'edit_post', $post->ID ) ) {
        return $actions;
    }
    $url = wp_nonce_url(
        add_query_arg( array( 'action' => 'ffl_duplicate_einsatz', 'post' => $post->ID ), admin_url( 'admin.php' ) ),
        'ffl_duplicate_einsatz_' . $post->ID
    );
    $actions['ffl_duplicate'] = '<a href="' . esc_url( $url ) . '">Duplizieren</a>';
    return $actions;
}

add_action( 'admin_action_ffl_duplicate_einsatz', 'ffl_duplicate_einsatz' );
function ffl_duplicate_einsatz() {
    $post_id = absint( $_GET['post'] ?? 0 );
    check_admin_referer( 'ffl_duplicate_einsatz_' . $post_id );
    if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
        wp_die( 'Keine Berechtigung zum Duplizieren dieses Einsatzes.' );
    }

    $source = get_post( $post_id );
    if ( ! $source || $source->post_type !== 'ffl_einsatz' ) {
        wp_die( 'Der Einsatz wurde nicht gefunden.' );
    }

    $new_id = wp_insert_post(
        array(
            'post_type'      => 'ffl_einsatz',
            'post_status'    => 'draft',
            'post_title'     => $source->post_title . ' – Kopie',
            'post_content'   => $source->post_content,
            'post_excerpt'   => $source->post_excerpt,
            'post_author'    => get_current_user_id(),
            'comment_status' => $source->comment_status,
            'ping_status'    => $source->ping_status,
        ),
        true
    );
    if ( is_wp_error( $new_id ) ) {
        wp_die( esc_html( $new_id->get_error_message() ) );
    }

    $terms = wp_get_object_terms( $post_id, 'ffl_einsatzart', array( 'fields' => 'ids' ) );
    if ( ! is_wp_error( $terms ) ) {
        wp_set_object_terms( $new_id, $terms, 'ffl_einsatzart' );
    }
    $all_meta = get_post_meta( $post_id );
    foreach ( $all_meta as $key => $values ) {
        if ( in_array( $key, array( '_edit_lock', '_edit_last' ), true ) ) {
            continue;
        }
        foreach ( $values as $value ) {
            add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
        }
    }

    wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_id . '&ffl_duplicated=1' ) );
    exit;
}

add_action( 'admin_notices', 'ffl_duplicate_notice' );
function ffl_duplicate_notice() {
    $screen = get_current_screen();
    if ( empty( $_GET['ffl_duplicated'] ) || ! $screen || $screen->post_type !== 'ffl_einsatz' ) {
        return;
    }
    echo '<div class="notice notice-success is-dismissible"><p>Der Einsatz wurde vollständig als Entwurf dupliziert.</p></div>';
}
