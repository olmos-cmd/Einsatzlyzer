<?php
$root = dirname(__DIR__);
$module = file_get_contents($root . '/includes/vehicles-depesche.php');
$main = file_get_contents($root . '/einsatz-manager.php');
$impex = file_get_contents($root . '/includes/import-export.php');
$checks = array(
    'raw identifier not editable' => strpos($main, 'ffl_leitstellen_einsatznummer') === false,
    'raw identifier not mapped' => strpos($module, "'control_number' => '_ffl_leitstellen_einsatznummer'") === false,
    'identifier masked in preview' => strpos($module, 'ffl_dispatch_mask_control_number') !== false,
    'only hash persisted' => strpos($module, '_ffl_dispatch_control_hash') !== false,
    'old raw meta deleted' => strpos($module, "delete_post_meta( \$target, '_ffl_leitstellen_einsatznummer' )") !== false,
    'full backup contract uses hash' => strpos($impex, '_ffl_dispatch_control_hash') !== false && strpos($impex, '_ffl_leitstellen_einsatznummer') === false,
    'opened incident redirect retained' => strpos($module, "admin_url( 'post.php' )") !== false,
);
foreach ($checks as $label => $ok) { if (!$ok) { fwrite(STDERR, "FAILED: $label\n"); exit(1); } }
echo "dispatch-confidentiality-test: OK\n";
