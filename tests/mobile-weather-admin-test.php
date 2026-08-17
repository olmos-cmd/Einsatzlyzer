<?php
/** Static integration checks for Einsatzlyzer 10.4.4 mobile weather administration. */
$plugin = file_get_contents( dirname( __DIR__ ) . '/einsatz-manager.php' );
$checks = array(
    'Mobile weather list' => false !== strpos( $plugin, 'ffl-weather-mobile-list' ),
    'Mobile weather card' => false !== strpos( $plugin, 'ffl-weather-mobile-card' ),
    'Desktop table retained' => false !== strpos( $plugin, 'ffl-weather-desktop-table' ),
    'Responsive breakpoint' => false !== strpos( $plugin, 'max-width:782px' ),
    'Full-width mobile button' => false !== strpos( $plugin, '.ffl-weather-mobile-card .button' ),
    'Dual result updates' => false !== strpos( $plugin, 'cells=id=>' ),
);
foreach ( $checks as $label => $passed ) {
    echo ( $passed ? '[OK] ' : '[FAIL] ' ) . $label . PHP_EOL;
    if ( ! $passed ) exit( 1 );
}
