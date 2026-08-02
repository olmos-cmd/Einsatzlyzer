<?php
$plugin = file_get_contents( dirname(__DIR__) . '/einsatz-manager.php' );
$checks = array(
 'incident coordinates preferred' => strpos($plugin, "'source'    => 'incident'") !== false,
 'default location fallback' => strpos($plugin, "'source'    => 'default_location'") !== false,
 'generic coordinate validation' => strpos($plugin, 'ffl_is_valid_coordinate_pair') !== false,
 'no hard coded Jümme resolver' => strpos($plugin, 'ffl_validate_juemme_coordinates') === false,
);
foreach ($checks as $name=>$ok) { echo ($ok?'OK ':'FAIL ').$name.PHP_EOL; if(!$ok) exit(1); }
