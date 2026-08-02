<?php
$root = dirname( __DIR__ );
$main = file_get_contents( $root . '/einsatz-manager.php' );
$impex = file_get_contents( $root . '/includes/import-export.php' );
$distance = file_get_contents( $root . '/includes/distance-routing.php' );
$checks = array(
    'Version 10.6.12' => false !== strpos( $main, "FFL_EINSATZLYZER_VERSION', '10.6.12" ),
    'Schema version 6' => false !== strpos( $impex, 'FFL_IMPEX_SCHEMA_VERSION = 6' ),
    'Routing backup contract' => false !== strpos( $impex, 'function ffl_impex_routing_backup_contract' ),
    'Distance setting backed up' => false !== strpos( $impex, "'ffl_distance_mode'" ),
    'Road distance meta backed up' => false !== strpos( $impex, "'_ffl_route_road_km'" ),
    'Travel time meta backed up' => false !== strpos( $impex, "'_ffl_route_duration_min'" ),
    'Routing errors backed up' => false !== strpos( $impex, "'_ffl_route_last_error'" ),
    'Routing settings verified' => false !== strpos( $impex, 'ffl_impex_verify_routing_settings_restore' ),
    'English network error' => false !== strpos( $distance, "ffl_lang( 'Netzwerkfehler', 'Network error' )" ),
    'English settings labels' => false !== strpos( $main, "ffl_lang( 'Ausgangspunkt Feuerwehrhaus', 'Fire station starting point' )" ),
);
$failed = array_keys( array_filter( $checks, static fn( $ok ) => ! $ok ) );
if ( $failed ) { fwrite( STDERR, 'FAILED: ' . implode( ', ', $failed ) . PHP_EOL ); exit( 1 ); }
echo "Routing backup and translation checks passed.\n";
