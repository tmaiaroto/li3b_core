<?php
use lithium\core\Libraries;

/**
 * This includes all connection configurations added in the `config/connections`
 * directory of the main application.
 * 
 * This allows for this file to remain coflict free and allows the li3 console
 * command to create new files in that directory rather than trying to modify
 * this one, which could also lead to conflicts and other issues.
 * 
 * These connection configuration files are included in alphabetical order.
 * 
 * Lithium Bootstrap will put conventional emphasis on the following
 * connection configuration names:
 * li3b_mongodb
 * li3b_mysql
 * li3b_redis
 * 
 * and so on...
 * 
 * So add on libraries wishing to use Lithium Bootstrap, should consider
 * using these connection names in their models. They should also consider
 * prefixing their model $_meta['source'] values to use prefixed names for
 * tables/collections to avoid conflicts since using a conventional/default
 * connection will mean multiple libraries using the same database.
 * For example, if two libraries have a `User` model, they would conflict
 * if both used the default `users` table/collection. A better idea would
 * be to set $_meta['source'] = 'libName.users' or 'libName_users' etc.
 * Not only does this avoid conflict (since there can not be two library
 * directories by the same name), but it also immediately clues a developer
 * into which tables/collections are used by which library when looking
 * at the database.
 * 
*/

/**
 * But first, we'll add a default connection.
 * MongoDB makes the most sense here because it often does not
 * require a username and password. Lithium Bootstrap can be
 * used with any database, but MongoDB is the preferred database.
 */
Connections::add(
	'li3b_mongodb', array(
		'production' => array(
			'type' => 'MongoDb',
			'host' => 'localhost',
			'database' => 'li3bootstrap'
		),
		'development' => array(
			'type' => 'MongoDb',
			'host' => 'localhost',
			'database' => 'li3bootstrap_dev'
		),
		'test' => array(
			'type' => 'database', 
			'adapter' => 'MongoDb', 
			'database' => 'li3bootstrap_test', 
			'host' => 'localhost'
		)
	)
);

$appConfig =  Libraries::get(true);
$connd = $appConfig['path'] . '/config/bootstrap/connections/*.php';
$conndFiles = glob($connd);
if(!empty($conndFiles)) {
	asort($conndFiles);
}

foreach ($conndFiles as $filename) {
	include $filename;
}
?>