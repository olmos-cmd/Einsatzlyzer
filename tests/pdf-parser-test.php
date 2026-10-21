<?php
$root = dirname(__DIR__);
$parser = file_get_contents($root . '/includes/simple-pdf-text.php');
$dispatch = file_get_contents($root . '/includes/vehicles-depesche.php');
$checks = array(
    'pure PHP parser bundled' => false !== strpos($parser, 'class FFL_Simple_PDF_Text'),
    'parser used first' => false !== strpos($dispatch, 'new FFL_Simple_PDF_Text'),
    'pdftotext optional fallback' => false !== strpos($dispatch, 'optionaler Fallback'),
    'OCR message' => false !== strpos($dispatch, 'OCR'),
);
foreach ($checks as $label => $ok) { echo ($ok ? '[OK] ' : '[FAIL] ') . $label . PHP_EOL; if (!$ok) exit(1); }
