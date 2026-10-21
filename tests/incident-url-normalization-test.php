<?php
/** Static regression checks for Einsatzlyzer-only trailing-slash normalization. */
$root   = dirname( __DIR__ );
$main   = file_get_contents( $root . '/einsatz-manager.php' );
$single = file_get_contents( $root . '/single-ffl_einsatz.php' );
$seo    = file_get_contents( $root . '/includes/seo-diagnostics.php' );
$impex  = file_get_contents( $root . '/includes/import-export.php' );
$checks = array(
    'Version 10.6.25' => false !== strpos( $main, "FFL_EINSATZLYZER_VERSION', '10.6.25" ),
    'central incident URL normalizer' => false !== strpos( $main, 'function ffl_normalize_incident_url' ) && false !== strpos( $main, 'function ffl_get_incident_permalink' ),
    'no global post type permalink filter' => false === strpos( $main, "add_filter( 'post_type_link'" ),
    'query and fragment preservation' => false !== strpos( $main, "\$fragment_pos = strpos( \$url, '#' )" ) && false !== strpos( $main, "\$query_pos = strpos( \$url, '?' )" ),
    'archive cards use helper' => substr_count( $main, 'ffl_get_incident_permalink( $post_id )' ) >= 4,
    'overview map uses helper' => false !== strpos( $main, "'url'      => ffl_get_incident_permalink( \$post->ID )" ),
    'single share links use one normalized URL' => false !== strpos( $single, '$incident_url  = ffl_get_incident_permalink( $post_id );' ) && false === strpos( $single, 'get_permalink()' ),
    'canonical diagnostics compare exact URLs' => false !== strpos( $seo, "esc_url_raw( trim( \$custom_canonical ) ) !== esc_url_raw( \$permalink )" ) && false === strpos( $seo, 'untrailingslashit( $custom_canonical )' ),
    'schema and live inspection use helper' => substr_count( $seo, 'ffl_get_incident_permalink( $post_id )' ) >= 3,
    'CSV uses helper' => false !== strpos( $impex, 'ffl_get_incident_permalink( $post_id )' ),
);
foreach ( $checks as $label => $ok ) {
    echo ( $ok ? '[OK] ' : '[FAIL] ' ) . $label . PHP_EOL;
    if ( ! $ok ) { exit( 1 ); }
}
