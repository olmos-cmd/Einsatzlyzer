<?php
$plugin=file_get_contents(dirname(__DIR__).'/einsatz-manager.php');
$checks=[
'Version 10.6.13'=>str_contains($plugin,"10.6.13"),
'Persistent registry'=>str_contains($plugin,'ffl_weather_error_registry'),
'Dashboard tabs'=>str_contains($plugin,"'statistics'=>ffl_lang"),
'Search filter'=>str_contains($plugin,'ffl-weather-search'),
'Individual retry'=>str_contains($plugin,'Diesen Einsatz erneut versuchen'),
'Runtime controls hidden'=>str_contains($plugin,'id="ffl-weather-runtime" hidden'),
];foreach($checks as $n=>$ok){echo ($ok?'OK ':'FAIL ').$n.PHP_EOL;if(!$ok)exit(1);}
