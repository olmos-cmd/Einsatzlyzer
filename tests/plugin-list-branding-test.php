<?php
$main = file_get_contents( dirname( __DIR__ ) . '/einsatz-manager.php' );
$checks = array(
    'Version 10.6.13' => false !== strpos( $main, "FFL_EINSATZLYZER_VERSION', '10.6.13" ),
    'Plugin list branding hook' => false !== strpos( $main, "admin_head-plugins.php" ),
    'Existing icon asset' => false !== strpos( $main, 'images/branding/einsatzlyzer-icon.png' ),
    'Updated description' => false !== strpos( $main, 'Moderne Feuerwehr-Einsatzverwaltung mit Wettermodul' ),
);
foreach ( $checks as $name => $ok ) {
    echo ( $ok ? '[OK] ' : '[FAIL] ' ) . $name . PHP_EOL;
    if ( ! $ok ) { exit(1); }
}
