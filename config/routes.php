<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * The routes file is where you define your URL structure, which is an important part of the
 * [information architecture](http://en.wikipedia.org/wiki/Information_architecture) of your
 * application. Here, you can use _routes_ to match up URL pattern strings to a set of parameters,
 * usually including a controller and action to dispatch matching requests to. For more information,
 * see the `Router` and `Route` classes.
 *
 * @see lithium\net\http\Router
 * @see lithium\net\http\Route
 */
use lithium\net\http\Router;
use lithium\core\Environment;
use lithium\action\Dispatcher;

// Set the evironment
if($_SERVER['HTTP_HOST'] == 'li3bootstrap.dev.local' || $_SERVER['HTTP_HOST'] == 'li3bootstrap.local' || $_SERVER['HTTP_HOST'] == 'localhost') {
	Environment::set('development');
}

/**
 * Dispatcher rules to rewrite admin actions.
 */
Dispatcher::config(array(
	'rules' => array(
		'admin' => array('action' => 'admin_{:action}')
	)
));

/**
 * "/admin" is the prefix for all Lithium Bootstrap admin routes.
 * Any "plugin" or library written for use with Lithium Bootstrap can utilize these routes
 * without needing to write any additional routes in most cases as this handles the basic CRUD.
 * It also handles pagination.
 *
 * Admin pages can be added to the main app's "/views/_libraries/li3b_core/pages" directory
 * and are accessible viw /admin/page/{:args}
 *
 * NOTE: li3b_core has no controller other than the pages controller. Other libraries and the
 * main application are where controllers belong. li3b_core is just the skeleton and assumes
 * no functionality. Static pages are as far as it goes.
 */
Router::connect("/admin", array('admin' => true, 'controller' => 'pages', 'action' => 'view', 'args' => array()), array('persist' => array(
	'controller', 'admin'
)));
Router::connect("/admin/page/{:args}", array('admin' => true, 'controller' => 'pages', 'action' => 'view', 'args' => array()), array('persist' => array(
	'controller', 'admin'
)));

/**
 * There are a bunch of options here for maximum flexibility.
 * For example, all of the following are valid URLs that can be routed:
 * /admin/plugin/li3b_users/users
 * /admin/plugin/li3b_users/users/index
 * /admin/plugin/li3b_users/users/index/page-1
 * /admin/plugin/li3b_users/users/index/page-1/limit-1
 * /admin/plugin/li3b_users/users/index/page-1/sort-role,asc
 * /admin/plugin/li3b_users/users/index/page-1/limit-1/sort-role,desc
 *
 * The following are not valid:
 * /admin/plugin/li3b_users/users/index/limit-1
 * /admin/plugin/li3b_users/users/index/sort-role,asc
 *
 * The reason for this is because there is no need to have a limit unless there are multiple pages.
 * Sort also doesn't make a lot of sense, though it would make at least a little more sense.
 * Even still, to reduce on the number of routes, a page must precede a limit.
 * If using Lithium's Html helper's link() method, then this is all done automatically.
 * Lithium Bootstrap's Pagination helper also picks up on these routes without any trouble.
 * Ultimately, a few routes could be removed in favor of the longest one...But the shorter routes
 * are kept in purely for the sake of having shorter routes which may look nicer to and end user.
 */
Router::connect('/admin/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}/limit-{:limit:[0-9]+}/sort-{:sort}/{:args}', array('admin' => true), array('persist' => array(
	'controller', 'admin', 'library'
)));
Router::connect('/admin/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}/limit-{:limit:[0-9]+}', array('admin' => true), array('persist' => array(
	'controller', 'admin', 'library'
)));
Router::connect('/admin/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}/sort-{:sort}', array('admin' => true), array('persist' => array(
	'controller', 'admin', 'library'
)));
Router::connect('/admin/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}/{:args}', array('admin' => true), array('persist' => array(
	'controller', 'admin', 'library'
)));
Router::connect('/admin/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}', array('admin' => true), array('persist' => array(
	'controller', 'admin', 'library'
)));
Router::connect("/admin/plugin/{:library}/{:controller}/{:action}/{:args}", array('admin' => true, 'action' => 'index', 'args' => array()), array('persist' => array(
	'controller', 'admin', 'library'
)));

/**
 * All libraries written for Lithium Bootstrap can have a "home page" of the sorts.
 * Essentially, a PagesController.php can be added that simply extends \li3b_core\controllers\PagesController
 * and then under the library's "/views/pages" directory an "admin_home.html.php" template can be added.
 * This can serve as a welcome page for example. Available via the following short route.
 * Of course additional pages will be routed using the route above which is lengthier.
 * ex. /admin/li3b_users/pages/view/home (the "home" page works for both URLs of course)
 */
Router::connect("/admin/plugin/{:library}", array('admin' => true, 'controller' => 'pages', 'action' => 'view', 'args' => array()), array('persist' => array(
	'controller', 'admin', 'library'
)));

/**
 * One of the ways Lithium Bootstrap speeds up development is by allowing the base application to take advantage
 * of the admin interface. The following routes will allow for this. Note there is no "plugin" prefix and the
 * {:library} has also been removed.
 *
 * NOTE: You will need an admin layout template in your main app's views directory (this may change in the future).
 */
Router::connect('/admin/{:controller}/{:action}/page-{:page:[0-9]+}/limit-{:limit:[0-9]+}/sort-{:sort}/{:args}', array('admin' => true), array('persist' => array(
	'controller', 'admin'
)));
Router::connect('/admin/{:controller}/{:action}/page-{:page:[0-9]+}/limit-{:limit:[0-9]+}', array('admin' => true), array('persist' => array(
	'controller', 'admin'
)));
Router::connect('/admin/{:controller}/{:action}/page-{:page:[0-9]+}/sort-{:sort}', array('admin' => true), array('persist' => array(
	'controller', 'admin'
)));
Router::connect('/admin/{:controller}/{:action}/page-{:page:[0-9]+}/{:args}', array('admin' => true), array('persist' => array(
	'controller', 'admin'
)));
Router::connect('/admin/{:controller}/{:action}/page-{:page:[0-9]+}', array('admin' => true), array('persist' => array(
	'controller', 'admin'
)));
Router::connect("/admin/{:controller}/{:action}/{:args}", array('admin' => true, 'action' => 'index', 'args' => array()), array('persist' => array(
	'controller', 'admin'
)));


/**
 * Naturally, we'd like some "public" non-admin routes to match.
 * This will make it easier for libraries written for Lithium Bootstrap
 * to take advantage of default routing and in some cases require no additional
 * or even duplicate routes to be written in each library's "routes.php" file.
 *
 * NOTE: This of course does not mean a plugin needs to have a public index action.
 * It also does not cover controllers in the base application. Note the "plugin" prefix.
 */
Router::connect('/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}/limit-{:limit:[0-9]+}/sort-{:sort}/{:args}', array('action' => 'index'), array('persist' => array(
	'controller', 'library'
)));
Router::connect('/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}/limit-{:limit:[0-9]+}', array('action' => 'index'), array('persist' => array(
	'controller', 'library'
)));
Router::connect('/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}/sort-{:sort}', array('action' => 'index'), array('persist' => array(
	'controller', 'library'
)));
Router::connect('/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}/{:args}', array('action' => 'index'), array('persist' => array(
	'controller', 'admin', 'library'
)));
Router::connect('/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}', array('action' => 'index'), array('persist' => array(
	'controller', 'admin', 'library'
)));
Router::connect("/plugin/{:library}/{:controller}/{:action}/{:args}", array('action' => 'index', 'args' => array()), array('persist' => array(
	'controller', 'library'
)));

/**
 * Connect the "public" static pages.
 * NOTE: This is something that might very well be overwritten by the main app's routes.
 *
 * Remember, li3b_core static pages can always be used with: /plugin/li3b_core/pages/view/home
 * So even if the main application wants to repurpose the "/" URL, it can still use core static pages
 * which can have template overrides in the main app's views directory at: /views/_libraries/li3b_core/pages/...
 */
Router::connect("/", array('library' => 'li3b_core', 'controller' => 'pages', 'action' => 'view', 'args' => array('home'), 'persist' => false));
Router::connect("/page/{:args}", array('library' => 'li3b_core', 'controller' => 'pages', 'action' => 'view', 'args' => array('home'), 'persist' => false));

/**
 * Add the testing routes. These routes are only connected in non-production environments, and allow
 * browser-based access to the test suite for running unit and integration tests for the Lithium
 * core, as well as your own application and any other loaded plugins or frameworks. Browse to
 * [http://path/to/app/test](/test) to run tests.
 */
if (!Environment::is('production')) {
	Router::connect('/test/{:args}', array('controller' => 'lithium\test\Controller'));
	Router::connect('/test', array('controller' => 'lithium\test\Controller'));
}
?>