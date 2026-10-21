<?php
/** Static regression checks for the server-rendered homepage incident teaser (10.6.19). */
$root = dirname( __DIR__ );
$main = file_get_contents( $root . '/einsatz-manager.php' );
$css = file_get_contents( $root . '/css/einsatzlyzer.css' );
$impex = file_get_contents( $root . '/includes/import-export.php' );
$checks = array(
    'version 10.6.25' => false !== strpos( $main, "FFL_EINSATZLYZER_VERSION', '10.6.25" ),
    'homepage shortcode registered' => false !== strpos( $main, "add_shortcode( 'ffl_startseite_einsaetze'" ),
    'server-side renderer' => false !== strpos( $main, 'function ffl_render_home_incidents_shortcode' ),
    'enabled setting' => false !== strpos( $main, "'ffl_home_incidents_enabled'" ),
    'count setting' => false !== strpos( $main, "'ffl_home_incidents_count'" ),
    'count sanitizer 1-5' => false !== strpos( $main, 'function ffl_sanitize_home_incidents_count' ),
    'incident permalink helper' => false !== strpos( $main, '$url       = ffl_get_incident_permalink( $post_id );' ),
    'archive url helper' => false !== strpos( $main, '$archive_url = ffl_get_archive_url();' ),
    'featured image fallback' => false !== strpos( $main, 'has_post_thumbnail( $post_id )' ),
    'report image fallback' => false !== strpos( $main, 'function ffl_get_first_report_image_url' ),
    'gallery fallback' => false !== strpos( $main, "_ffl_gallery_ids" ),
    'no javascript requirement' => false !== strpos( $main, 'Der Startseiten-Teaser ist vollständig serverseitig und benötigt kein JS.' ),
    'responsive card css' => false !== strpos( $css, '.ffl-home-incidents__grid' ) && false !== strpos( $css, '@media(max-width:680px)' ),
    'dark overlay css' => false !== strpos( $css, '.ffl-home-incident-card__link::before' ) && false !== strpos( $css, 'linear-gradient' ),
    'title clamp' => false !== strpos( $css, '-webkit-line-clamp:3' ) && false !== strpos( $css, 'overflow-wrap:anywhere' ),
    'backup contract' => false !== strpos( $impex, 'function ffl_impex_home_incidents_backup_contract' ),
    'manifest contract' => false !== strpos( $impex, "'homepage_incidents' => ffl_impex_home_incidents_backup_contract()" ),
    'restore verification' => false !== strpos( $impex, 'ffl_impex_verify_home_incidents_settings_restore' ),
    'schema remains 7' => false !== strpos( $impex, 'FFL_IMPEX_SCHEMA_VERSION = 7' ),
);
foreach ( $checks as $label => $passed ) {
    echo ( $passed ? '[OK] ' : '[FAIL] ' ) . $label . PHP_EOL;
}
exit( in_array( false, $checks, true ) ? 1 : 0 );
