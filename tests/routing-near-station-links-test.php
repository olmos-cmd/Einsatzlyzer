<?php
$root = dirname( __DIR__ );
$distance = file_get_contents( $root . '/includes/distance-routing.php' );
$main = file_get_contents( $root . '/einsatz-manager.php' );
$single = file_get_contents( $root . '/single-ffl_einsatz.php' );
$impex = file_get_contents( $root . '/includes/import-export.php' );
$checks = array(
    'Version 10.6.13' => false !== strpos( $main, "FFL_EINSATZLYZER_VERSION', '10.6.13" ),
    'Near station threshold' => false !== strpos( $distance, '$air_exact_km <= 0.1' ),
    'Near station route avoids OSRM' => false !== strpos( $distance, "'near_station' => true" ),
    'Batch errors contain edit URL' => false !== strpos( $distance, "'edit_url' => get_edit_post_link" ),
    'Source field rendered' => false !== strpos( $main, 'ffl_link_source_' ),
    'Source field saved' => false !== strpos( $main, '$source_field' ),
    'Source displayed in frontend' => false !== strpos( $single, "['source']" ),
    'All _ffl meta exported' => false !== strpos( $impex, "0 !== strpos( \$key, '_ffl_' )" ),
);
$failed = array_keys( array_filter( $checks, static fn( $ok ) => ! $ok ) );
if ( $failed ) { fwrite( STDERR, "FAILED: " . implode( ', ', $failed ) . PHP_EOL ); exit( 1 ); }
echo "OK routing near-station and link sources\n";
