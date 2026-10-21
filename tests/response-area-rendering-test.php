<?php
/**
 * Regression test for response-area Leaflet SVG rendering (10.6.19).
 */
$root = dirname(__DIR__);
$css  = file_get_contents($root . '/css/einsatzlyzer.css');
$js   = file_get_contents($root . '/js/einsatzlyzer.js');

$checks = array(
    'Leaflet overlay SVG width reset' => strpos($css, '.leaflet-container .leaflet-overlay-pane svg') !== false && strpos($css, 'width: auto;') !== false,
    'Leaflet overlay SVG height reset' => strpos($css, 'height: auto;') !== false,
    'Dedicated polygon CSS selector' => strpos($css, 'path.ffl-response-area-polygon') !== false,
    'Dedicated polygon JS class' => strpos($js, "className: 'ffl-response-area-polygon'") !== false,
    'Polygon still added to map' => strpos($js, 'L.polygon(points') !== false && strpos($js, '}).addTo(map);') !== false,
);

$failed = false;
foreach ($checks as $label => $ok) {
    echo ($ok ? '[OK] ' : '[FAIL] ') . $label . PHP_EOL;
    if (!$ok) $failed = true;
}
exit($failed ? 1 : 0);
