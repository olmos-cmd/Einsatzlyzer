<?php
/** Static regression test for the complete weather backup contract. */
$root   = dirname( __DIR__ );
$code   = file_get_contents( $root . '/includes/import-export.php' );
$main   = file_get_contents( $root . '/einsatz-manager.php' );
$checks = array(
    'Plugin version 10.6.11' => false !== strpos( $main, "10.6.11" ),
    'Backup schema 6' => false !== strpos( $code, 'FFL_IMPEX_SCHEMA_VERSION = 6' ),
    'Weather backup contract' => false !== strpos( $code, 'ffl_impex_weather_backup_contract' ),
    'Station name setting' => false !== strpos( $code, "'ffl_station_name'" ),
    'Station latitude setting' => false !== strpos( $code, "'ffl_station_lat'" ),
    'Station longitude setting' => false !== strpos( $code, "'ffl_station_lon'" ),
    'Weather payload metadata' => false !== strpos( $code, "'_ffl_weather_data'" ),
    'Coordinate source field' => false !== strpos( $code, "'coordinate_source'" ),
    'Coordinate label field' => false !== strpos( $code, "'coordinate_label'" ),
    'Restore verification' => false !== strpos( $code, 'ffl_impex_verify_weather_settings_restore' ),
);
$failed = array_keys( array_filter( $checks, static fn( $ok ) => ! $ok ) );
if ( $failed ) {
    fwrite( STDERR, "FAILED: " . implode( ', ', $failed ) . PHP_EOL );
    exit( 1 );
}
echo "Weather import/export contract: OK" . PHP_EOL;
