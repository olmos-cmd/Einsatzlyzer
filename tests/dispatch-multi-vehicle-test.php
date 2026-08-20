<?php
$code = file_get_contents( dirname( __DIR__ ) . '/includes/vehicles-depesche.php' );
$checks = array(
    'all call signs per line' => false !== strpos( $code, "preg_match_all( '/\\b(?:FL\\s+[A-Z]{2,4}\\s+)?(\\d{2}-\\d{1,2}-\\d{1,2})" ),
    'matched vehicle ids' => false !== strpos( $code, 'matched_vehicle_ids' ),
    'all matched vehicle text' => false !== strpos( $code, 'matched_vehicle_text' ),
    'incident vehicle links saved' => false !== strpos( $code, "update_post_meta( \$target, '_ffl_vehicle_ids'" ),
    'recognized vehicles preview' => false !== strpos( $code, 'Erkannte Fahrzeuge' ),
);
foreach ( $checks as $label => $ok ) {
    echo ( $ok ? '[OK] ' : '[FAIL] ' ) . $label . PHP_EOL;
    if ( ! $ok ) exit( 1 );
}
