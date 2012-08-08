<?php
use lithium\core\Libraries;

/**
 * This includes all libraries added in the `config/libraries` directory
 * of the main application.
 * 
 * This allows for this file to remain coflict free and allows the li3 console
 * command to create new files in that directory rather than trying to modify
 * this one, which could also lead to conflicts and other issues.
*/

$appConfig =  Libraries::get(true);
$libd = $appConfig['path'] . '/config/bootstrap/libraries/*.php';

foreach (glob($libd) as $filename) {
	include $filename;
}
?>