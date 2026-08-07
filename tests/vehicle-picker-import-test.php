<?php
$root = dirname(__DIR__);
$vehicles = file_get_contents($root . '/includes/vehicles-depesche.php');
$manager = file_get_contents($root . '/einsatz-manager.php');
$impex = file_get_contents($root . '/includes/import-export.php');
$checks = array(
    'picker renderer' => strpos($vehicles, 'ffl_render_incident_vehicle_picker') !== false,
    'standalone vehicle import' => strpos($vehicles, 'ffl_vehicle_registry_import_handler') !== false,
    'municipality metadata' => strpos($vehicles, '_ffl_vehicle_municipality') !== false,
    'selected vehicle ids' => strpos($manager, '_ffl_vehicle_ids') !== false,
    'schema 6' => strpos($impex, 'FFL_IMPEX_SCHEMA_VERSION = 6') !== false,
    'vehicle UUID relationships' => strpos($impex, "'vehicles'") !== false && strpos($impex, 'vehicle_uuid_map') !== false,
);
foreach ($checks as $name => $ok) {
    echo ($ok ? '[OK] ' : '[FAIL] ') . $name . PHP_EOL;
    if (!$ok) exit(1);
}
