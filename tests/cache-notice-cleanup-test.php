<?php
$root = dirname(__DIR__);
$main = file_get_contents($root . '/einsatz-manager.php');
$diag = file_get_contents($root . '/includes/seo-diagnostics.php');
$import = file_get_contents($root . '/includes/import-export.php');
$checks = array(
    'Version 10.6.13' => false !== strpos($main, "FFL_EINSATZLYZER_VERSION', '10.6.13"),
    'Tools menu removed' => false === strpos($main, 'ffl_einsatz_werkzeuge'),
    'Legacy cleanup handler removed' => false === strpos($main, 'ffl_cleanup_legacy_notice'),
    'Legacy import mutation removed' => false === strpos($import, 'ffl_remove_legacy_missing_details_notice'),
    'Notice restricted to Einsatzlyzer screens' => false !== strpos($diag, 'ffl_is_einsatzlyzer_admin_screen'),
    'Later action exists' => false !== strpos($diag, "ffl_lang( 'Später', 'Later' )"),
    'Generic WordPress page-cache label removed' => false === strpos($diag, 'WordPress-Seitencache'),
);
foreach ($checks as $name => $ok) {
    echo ($ok ? '[OK] ' : '[FAIL] ') . $name . PHP_EOL;
    if (!$ok) exit(1);
}
