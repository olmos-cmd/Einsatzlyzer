<?php
$root = dirname( __DIR__ );
$main = file_get_contents( $root . '/einsatz-manager.php' );
$export = file_get_contents( $root . '/includes/annual-report-export.php' );
$checks = array(
    'Version 10.6.11' => false !== strpos( $main, "FFL_EINSATZLYZER_VERSION', '10.6.11" ),
    'Annual report include' => false !== strpos( $main, 'includes/annual-report-export.php' ),
    'Server-side generation' => false !== strpos( $export, 'ffl_create_annual_report_file' ),
    'Secure download handler' => false !== strpos( $export, 'ffl_download_annual_report' ),
    'PDF writer' => false !== strpos( $export, 'ffl_write_annual_report_pdf' ),
    'CSV writer' => false !== strpos( $export, 'ffl_write_annual_report_csv' ),
    'XLSX writer' => false !== strpos( $export, 'ffl_write_annual_report_xlsx' ),
    'Browser print fallback' => false !== strpos( $export, 'ffl_print_annual_report' ),
    'Visible export errors' => false !== strpos( $export, 'ffl_report_error' ),
    'One-hour protected file token' => false !== strpos( $export, 'HOUR_IN_SECONDS' ),
);
foreach ( $checks as $label => $ok ) {
    echo ( $ok ? '[OK] ' : '[FAIL] ' ) . $label . PHP_EOL;
    if ( ! $ok ) { exit( 1 ); }
}
