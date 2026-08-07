<?php
$main=file_get_contents(dirname(__DIR__).'/einsatz-manager.php');
$css=file_get_contents(dirname(__DIR__).'/css/einsatzlyzer-admin.css');
$checks=[
'quality column'=>str_contains($main,"'ffl_quality'"),
'quality helper'=>str_contains($main,'function ffl_admin_quality_status'),
'quality states'=>str_contains($main,'quality_ready') && str_contains($main,'quality_almost') && str_contains($main,'quality_incomplete'),
'expandable details'=>str_contains($main,'ffl-quality__panel'),
'quality css'=>str_contains($css,'.ffl-quality--ready'),
];
foreach($checks as $name=>$ok){echo ($ok?'OK ':'FAIL ').$name.PHP_EOL;if(!$ok)exit(1);}
