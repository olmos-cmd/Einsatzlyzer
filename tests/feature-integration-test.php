<?php
/** Static integration checks for Einsatzlyzer 10.4.3. */
$plugin = file_get_contents( dirname( __DIR__ ) . '/einsatz-manager.php' );
$template = file_get_contents( dirname( __DIR__ ) . '/single-ffl_einsatz.php' );
$checks = array(
    'version' => str_contains( $plugin, "FFL_EINSATZLYZER_VERSION', '10.6.13" ),
    'annual_statistics' => str_contains( $plugin, "add_shortcode( 'ffl_jahresstatistik'" ),
    'weather_api' => str_contains( $plugin, 'archive-api.open-meteo.com/v1/archive' ),
    'weather_admin' => str_contains( $plugin, "'ffl_einsatz_wetter'" ),
    'related_scoring' => str_contains( $plugin, 'function ffl_related_incident_ids' ),
    'weather_batch_queue' => str_contains( $plugin, "wp_ajax_ffl_weather_queue" ),
    'weather_batch_controls' => str_contains( $plugin, "Fehlende abrufen" ),
    'print_view' => str_contains( $template, 'window.print()' ),
);
foreach ( $checks as $name => $passed ) { echo $name . ': ' . ( $passed ? 'OK' : 'FAILED' ) . PHP_EOL; if ( ! $passed ) exit( 1 ); }
