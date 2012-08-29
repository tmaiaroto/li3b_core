<?php
/**
 * The following filter allows any library's templates to be overridden.
 * This allows for greater control over the look of your application when
 * using 3rd party libraries without mucking around in the library code
 * which can, of course, lead to all sort of maintenance issues.
 *
 * Ultimately, we need to allow the following to happen:
 * 1. If there are no override templates, then the layout and view template will be used from the library (default behavior).
 * 2. If there are templates placed in `/views/_libraries/library_name/`, use those.
 * 3. Try the main application, because we don't want to change how Lithium works by default.
 * 4. If Libraries::add() passes a config that specifically says to use Lithium Bootstrap layouts, do so (forced situation, but there's still a fallback).
 *
 * Also, we need to consider elements and how they may need to work.
 * A similar system is provided for elements as well.
 *
 * Note: That this is all assuming a library is being used.
 * The main application will be completely unaffected.
 * This means that if you wish to use Lithium Bootstrap layouts, elements, etc.
 * in your main application, you will need to pass a library key of `li3b_core`
 * in many cases. For example, elements.
 *
 * Example:
 * $this->_render('element', 'navbar', array('user' => $this->request()->user), array('library' => 'li3b_core'));
 *
 * This would render the `nvarbar` element from Lithium Bootstrap. If the
 * main application also has a `navbar` element, it won't conflict. This means
 * all other libraries wishing to use this element should specify the library.
 * This is how you normally need to do it. Lithium Bootstrap isn't trying to
 * take over your Lithium application. However, when it can, it will try to
 * be flexible and fall back to a default in case you forget or don't want
 * to specify a library.
 *
 * For further example, libraries by default will render their own
 * templates, layouts, and elements because the request will have the
 * library set and the rendering system will use that by default.
 *
 * -- That's how Lithium works by default. We aren't changing that. --
 *
 * However, since we want to allow libraries to be built for and use
 * Lithium Bootstrap, we have the fallback to templates under li3b_core
 * when possible. That means if you somehow installed a library like that
 * without Lithium Bootstrap, you'd have missing templates.
 *
 * Moral of the story here is that if you are making a library for use
 * with Lithium Bootstrap, you should let people know...OR you should
 * provide all of the templates you need so that it can render pages on
 * its own. That means copying all of the layouts, CSS, JavaScript, etc.
 * Yea...A lot of duplication. That's why this template system filter
 * exists on the Dispatcher.
 */
use lithium\action\Dispatcher;
use lithium\core\Libraries;

Dispatcher::applyFilter('_callable', function($self, $params, $chain) {

	//var_dump($params['params']);
	//exit();

	if(isset($params['params']['library'])) {
		// Instead of using LITHIUM_APP_PATH,for future compatibility.
		$defaultAppConfig = Libraries::get(true);
		$appPath = $defaultAppConfig['path'];

		$libConfig = Libraries::get($params['params']['library']);

		/**
		 * LAYOUTS AND TEMPLATES
		 * Note the path ordering for how templates override others.
		 * First, your overrides and then the default render paths for a library.
		 * Second to last, it tries to grab what it can from the main application.
		 * Last (worst case) it tries to use what's in Lithium Bootstrap.
		 *
		 * The last scenario is rare, if using a "default" layout, for example,
		 * it likely exists in the main application already. If a library is
		 * specifcially designed for Lithium Bootstrap and wishes to use
		 * templates within li3b_core before looking in the main application,
		 * they should be added with the proper configuration settings.
		 */
		$paths['layout'] = array(
			$appPath . '/views/_libraries/' . $params['params']['library'] . '/layouts/{:layout}.{:type}.php',
			'{:library}/views/layouts/{:layout}.{:type}.php',
			$appPath . '/views/layouts/{:layout}.{:type}.php',
			// Last, look in the li3b_core library...
			$appPath . '/libraries/li3b_core/views/layouts/{:layout}.{:type}.php'
		);
		$paths['template'] = array(
			$appPath . '/views/_libraries/' . $params['params']['library'] . '/{:controller}/{:template}.{:type}.php',
			'{:library}/views/{:controller}/{:template}.{:type}.php',
			$appPath . '/views/{:controller}/{:template}.{:type}.php',
			// Last ditch effort to find the template...Note: Lithium Bootstrap takes a back seat to the main app.
			$appPath . '/libraries/li3b_core/views/{:controller}/{:layout}.{:type}.php'
		);

		/*
		 * Condition #4 here. This will prefer Lithium Bootstrap's core layouts.
		 * Libraries added with this configuration option were designed specifically
		 * for use with Lithium Bootstrap and wish to use it's default design.
		 *
		 * Of course, there is still template fallback support in case the user
		 * has changed up their copy of Lithium Bootstrap...But the library is
		 * now putting the priority on the Lithium Bootstrap layouts, unless
		 * overridden by templates in the _libraries directory of the main app.
		 *
		 * There is currently no need to do the same with templates since the
		 * li3b_core library has so few view templates...And they don't even make
		 * sense to share for any other purpose whereas layouts are definitely
		 * something another action can take advantage of.
		 */
		if(isset($libConfig['useBootstrapLayout']) && (bool)$libConfig['useBootstrapLayout'] === true) {
			$paths['layout'] = array(
				$appPath . '/views/_libraries/' . $params['params']['library'] . '/layouts/{:layout}.{:type}.php',
				$appPath . '/libraries/li3b_core/views/layouts/{:layout}.{:type}.php',
				'{:library}/views/layouts/{:layout}.{:type}.php',
				$appPath . '/views/layouts/{:layout}.{:type}.php'
			);
		}

		/**
		 * ELEMENTS
		 * This will allow the main application to still render it's elements
		 * even though the View() class may be dealing with one of this library's
		 * controllers, which would normally suggest the element comes from the library
		 * Again, note the ordering here for how things override others.
		 * 1. Your overrides are considered first.
		 * 2. Elements that may come with the library are used when a library key is used.
		 * 3. The main application is checked for the element templates (this functions as normal out of the box Lithium).
		 * 4. Lithium Bootstrap elements. Last ditch effort to find the element.
		 *    Note: When you wish to use an element from Lithium Bootstrap, you should
		 *    pass a library key to be certain it is used. Otherwise, if you have an
		 *    element in your main application by the same name as one from Lithium
		 *    Bootstrap, you could be using that instead when you did not intend to.
		 *    All of the elements rendered from li3b_core pass a library key and
		 *    your plugins, wishing to use core li3b elements, should do the same.
		 */
		$paths['element'] = array(
			$appPath . '/views/_libraries/' . $params['params']['library'] . '/elements/{:template}.{:type}.php',
			'{:library}/views/elements/{:template}.{:type}.php',
			$appPath . '/views/elements/{:template}.{:type}.php',
			$appPath . '/libraries/li3b_core/views/elements/{:template}.{:type}.php'
		);

		$params['options']['render']['paths'] = $paths;

	}

	return $chain->next($self, $params, $chain);
});
?>