<?php
/** Static regression checks for response-area polygon, settings and backup. */
$root = dirname( __DIR__ );
$main = file_get_contents( $root . '/einsatz-manager.php' );
$js = file_get_contents( $root . '/js/einsatzlyzer.js' );
$admin_js = file_get_contents( $root . '/js/einsatzlyzer-settings-admin.js' );
$impex = file_get_contents( $root . '/includes/import-export.php' );
$checks = array(
    'version 10.6.25' => false !== strpos( $main, "FFL_EINSATZLYZER_VERSION', '10.6.25" ),
    'coordinate setting' => false !== strpos( $main, "'ffl_response_area_coordinates'" ),
    'archive visibility setting' => false !== strpos( $main, "'ffl_response_area_show_archive'" ),
    'single visibility setting' => false !== strpos( $main, "'ffl_response_area_show_single'" ),
    'coordinate sanitizer' => false !== strpos( $main, 'function ffl_sanitize_response_area_coordinates' ),
    'settings modal' => false !== strpos( $main, 'id="ffl-response-area-modal"' ),
    'coordinate validation UI' => false !== strpos( $admin_js, 'parseResponseArea' ) && false !== strpos( $admin_js, 'validateResponseArea' ),
    'frontend localized polygon' => false !== strpos( $main, "'responseArea'    => ffl_get_response_area_config()" ),
    'leaflet polygon' => false !== strpos( $js, 'L.polygon(points' ),
    'archive map polygon' => false !== strpos( $js, "addResponseAreaPolygon(map, 'archive')" ),
    'single map polygon' => false !== strpos( $js, "addResponseAreaPolygon(map, 'single')" ),
    'backup contract' => false !== strpos( $impex, 'function ffl_impex_response_area_backup_contract' ),
    'manifest contract' => false !== strpos( $impex, "'response_area' => ffl_impex_response_area_backup_contract()" ),
    'restore verification' => false !== strpos( $impex, 'ffl_impex_verify_response_area_settings_restore' ),
    'schema remains 7' => false !== strpos( $impex, 'FFL_IMPEX_SCHEMA_VERSION = 7' ),
);
foreach ( $checks as $label => $passed ) {
    echo ( $passed ? '[OK] ' : '[FAIL] ' ) . $label . PHP_EOL;
}
exit( in_array( false, $checks, true ) ? 1 : 0 );
