<?php
$root = dirname( __DIR__ );
$main = file_get_contents( $root . '/einsatz-manager.php' );
$module = file_get_contents( $root . '/includes/distance-routing.php' );
$single = file_get_contents( $root . '/single-ffl_einsatz.php' );
$checks = array(
    'Version 10.6.13' => false !== strpos( $main, "FFL_EINSATZLYZER_VERSION', '10.6.13" ),
    'Six display modes' => false !== strpos( $module, "'air_road_time'" ) && false !== strpos( $module, "'road_time'" ),
    'OSRM route endpoint' => false !== strpos( $module, 'router.project-osrm.org/route/v1/driving/' ),
    'Queue AJAX' => false !== strpos( $module, "wp_ajax_ffl_route_queue" ),
    'Calculation AJAX' => false !== strpos( $module, "wp_ajax_ffl_route_calculate" ),
    'Progress UI' => false !== strpos( $module, 'ffl-route-progress' ),
    'Stored input hash' => false !== strpos( $module, '_ffl_route_input_hash' ),
    'Frontend distance output' => false !== strpos( $single, "distance['label']" ),
);
foreach ( $checks as $label => $ok ) {
    echo ( $ok ? '[OK] ' : '[FAIL] ' ) . $label . PHP_EOL;
    if ( ! $ok ) { exit( 1 ); }
}
