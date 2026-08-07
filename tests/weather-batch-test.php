<?php
/** Static integration checks for Einsatzlyzer 10.4.3 weather batch processing. */
$plugin = file_get_contents( dirname( __DIR__ ) . '/einsatz-manager.php' );
$checks = array(
    'Version 10.6.13' => false !== strpos( $plugin, "FFL_EINSATZLYZER_VERSION', '10.6.13" ),
    'Reusable fetch helper' => false !== strpos( $plugin, 'function ffl_fetch_and_store_weather' ),
    'Queue endpoint' => false !== strpos( $plugin, "wp_ajax_ffl_weather_queue" ),
    'Missing mode' => false !== strpos( $plugin, "data-mode=\"missing\"" ),
    'Refresh all mode' => false !== strpos( $plugin, "data-mode=\"all\"" ),
    'Retry errors mode' => false !== strpos( $plugin, "data-mode=\"errors\"" ),
    'Pause control' => false !== strpos( $plugin, 'ffl-weather-pause' ),
    'Resume control' => false !== strpos( $plugin, 'ffl-weather-resume' ),
    'Error metadata' => false !== strpos( $plugin, '_ffl_weather_last_error' ),
);
foreach ( $checks as $name => $passed ) { echo $name . ': ' . ( $passed ? 'OK' : 'FAILED' ) . PHP_EOL; if ( ! $passed ) exit( 1 ); }
