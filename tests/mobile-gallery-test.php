<?php
/** Static checks for the compact mobile gallery introduced in Einsatzlyzer 10.4.3. */
$root = dirname( __DIR__ );
$template = file_get_contents( $root . '/single-ffl_einsatz.php' );
$css = file_get_contents( $root . '/css/einsatzlyzer.css' );
$checks = array(
    'compact heading class' => false !== strpos( $template, 'ffl-gallery-heading' ),
    'single incident images heading' => false !== strpos( $template, "ffl_lang( 'Einsatzbilder', 'Incident Images' )" ),
    'mobile weather spacing' => false !== strpos( $css, '.ffl-weather-section{margin-bottom:18px}' ),
    'mobile gallery heading layout' => false !== strpos( $css, '.ffl-gallery-heading{display:flex' ),
);
foreach ( $checks as $label => $passed ) {
    echo ( $passed ? '[OK] ' : '[FAIL] ' ) . $label . PHP_EOL;
}
exit( in_array( false, $checks, true ) ? 1 : 0 );
