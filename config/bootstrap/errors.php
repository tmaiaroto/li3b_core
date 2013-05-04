<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use lithium\core\ErrorHandler;
use lithium\action\Response;
use lithium\net\http\Media;
use lithium\core\Environment;
use lithium\core\Libraries;

ErrorHandler::apply('lithium\action\Dispatcher::run', array(), function($info, $params) {
	$response = new Response(array(
		'request' => $params['request'],
		'status' => $info['exception']->getCode()
	));
	
	// Production error templates should follow the design of the site.
	$error_layout = 'default';
	$error_template = 'production';
	
	$appCfg = Libraries::get();
	$defaultApp = false;
	$defaultLibrary = null;
	foreach($appCfg as $library) {
		if($library['default'] === true) {
			$defaultApp = $library;
			$defaultLibrary = key($library);
		}
	}
	
	// Development error templates can look different.
	if(Environment::is('development')) {
		$error_layout = file_exists($defaultApp['path'] . '/views/layouts/error.html.php') ? 'error':$error_layout;
		$error_template = 'development';
	}
	
	// If the error templates don't exist use li3b_core's.
	$error_library = (file_exists($defaultApp['path'] . '/views/layouts/' . $error_layout . '.html.php') && file_exists($defaultApp['path'] . '/views/_errors/' . $error_template . '.html.php')) ? $defaultLibrary:'li3b_core';
	
	Media::render($response, compact('info', 'params'), array(
		'library' => $error_library,
		'controller' => '_errors',
		'template' => $error_template,
		'layout' => $error_layout,
		'request' => $params['request']
	));
	return $response;
});
?>