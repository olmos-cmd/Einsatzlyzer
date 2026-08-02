<?php
$main = file_get_contents(__DIR__ . '/../einsatz-manager.php');
$dep  = file_get_contents(__DIR__ . '/../includes/vehicles-depesche.php');
$seo  = file_get_contents(__DIR__ . '/../includes/seo-diagnostics.php');
$checks = array(
    'Version 10.6.12' => strpos($main, "FFL_EINSATZLYZER_VERSION', '10.6.12") !== false,
    'Setting registered' => strpos($main, "'ffl_dispatch_enabled'") !== false,
    'Settings checkbox' => strpos($main, 'Depeschen-Import aktivieren') !== false,
    'Menu conditional' => strpos($dep, 'if ( ffl_dispatch_import_enabled() )') !== false,
    'Metabox guard' => strpos($dep, 'if ( ! ffl_dispatch_import_enabled() ) return;') !== false,
    'Cache redirects to incident list' => strpos($seo, "admin_url( 'edit.php?post_type=ffl_einsatz' )") !== false,
);
foreach ($checks as $label=>$ok) { echo ($ok?'OK':'FAIL') . " - $label\n"; if(!$ok) exit(1); }
