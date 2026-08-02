<?php
$root = dirname(__DIR__);
$single = file_get_contents($root . '/single-ffl_einsatz.php');
$main = file_get_contents($root . '/einsatz-manager.php');
$checks = array(
    'Version 10.6.12' => false !== strpos($main, "FFL_EINSATZLYZER_VERSION', '10.6.12"),
    'Frontend incident source' => false !== strpos($single, "Datenquelle:") && false !== strpos($single, "Einsatzstelle"),
    'Frontend default source' => false !== strpos($single, "Feuerwehrhaus / Standardstandort"),
    'Admin dynamic source' => false !== strpos($main, "coordinate_source") && false !== strpos($main, "Feuerwehrhaus / Standardstandort"),
);
$failed = array_keys(array_filter($checks, static fn($ok) => !$ok));
if ($failed) { fwrite(STDERR, implode("\n", $failed) . "\n"); exit(1); }
echo "weather source notice tests passed\n";
