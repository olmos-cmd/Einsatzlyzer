<?php
$root = dirname(__DIR__);
$main = file_get_contents($root . '/einsatz-manager.php');
$impex = file_get_contents($root . '/includes/import-export.php');
$module = file_get_contents($root . '/includes/vehicles-depesche.php');
$checks = array(
    "Version 10.6.14" => strpos($main, "10.6.14") !== false,
    "vehicle post type" => strpos($module, "ffl_fahrzeug") !== false,
    "dispatch preview" => strpos($module, "ffl_render_dispatch_preview") !== false,
    "confidential control hash" => strpos($module, "_ffl_dispatch_control_hash") !== false,
    "raw control number excluded" => strpos($main, "ffl_leitstellen_einsatznummer") === false,
    "edit-screen preview redirect" => strpos($module, "admin_url( 'post.php' )") !== false,
    "backup schema 7" => strpos($impex, "FFL_IMPEX_SCHEMA_VERSION = 7") !== false,
    "vehicles json" => strpos($impex, "fahrzeuge.json") !== false,
    "vehicle images" => strpos($impex, "fahrzeugbilder/") !== false,
);
foreach ($checks as $label => $ok) { if (!$ok) { fwrite(STDERR, "FAILED: $label\n"); exit(1); } }
echo "vehicle-dispatch-import-export-test: OK\n";
