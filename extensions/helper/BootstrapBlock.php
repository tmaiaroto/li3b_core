<?php
/**
 * Block Helper
 *
 * This allows for various bits of code to be injected
 * into specific places in view and layout templates.
 *
 * Each "block" gets an identifier and then any other bit
 * of code, or library, that wants to inject content into
 * that area can do so.
 *
 * This is similar to the menu system where each library
 * applies a filter to adjust the array that builds menus.
 * This, of course, means that an ordering for content output
 * is also available. So even though a library may have its
 * content loaded first, it will have the opportunity to be
 * ordered in a desireable fashion.
 *
 * This does mean that libraries will need to understand
 * the ordering that other libraries are using, which may
 * be impossible. So simply have the main application apply
 * a re-ordering to things. The main application will have
 * it's filter called last and at that point, one could
 * simply re-order the array values however they wish.
 *
*/
namespace li3b_core\extensions\helper;

use li3b_core\models\BootstrapBlock as Block;
use lithium\template\View;
use lithium\core\Libraries;
use lithium\util\Inflector;
use lithium\storage\Cache;
use lithium\net\http\Router;

class BootstrapBlock extends \lithium\template\Helper {

	/**
	 * This renders a a block.
	 *
	 *
	 * @param string $position The block position identifier
	 * @param array $options
	 * @return string HTML code for the menu
	 */
	public function render($position=null, $data=array(), $options=array()) {
		$defaults = array(
			//'cache' => '+1 day'
			'cache' => false,
			'wrapperId' => false,
		);
		$options += $defaults;

		if(empty($position) || !is_string($position)) {
			return '';
		}

		// set the cache key for the menu
		$cache_key = 'li3b_blocks.' . $position;
		$blocks = false;

		// if told to use the block content from cache
		if(!empty($options['cache'])) {
			$blocks = Cache::read('default', $cache_key);
		}

		// if the content hasn't been set in cache or it was empty for some reason, get a fresh copy of its data
		if(empty($blocks)) {
			$blocks = Block::staticBlock($position);
		}

		// if using cache, write the menu data to the cache key
		if(!empty($options['cache'])) {
			Cache::write('default', $cache_key, $blocks, $options['cache']);
		}

		$string = "\n";
		if($options['wrapperId']) {
			$string .= '<div id="' . $options['wrapperId'] . '">';
		}
		foreach($blocks as $block) {
			if(isset($block['options']['wrapperId'])) {
				$string .= "\n\t" . '<div id="' . $block['options']['wrapperId'] . '">';
			}

			// Blocks can be very simple and contain all the content in the array.
			if(is_string($block['content'])) {
			$string .= "\n\t\t" . $block['content'];
			}

			// Or, they can point to an element view template. These are essentially like elements, only they can have layout templates as well.
			if(is_array($block['content'])) {
				if(isset($block['content']['template'])) {
					$elementOptions = isset($block['content']['options']) ? $block['content']['options']:array();
					if(isset($block['content']['library'])) {
						$elementOptions['library'] = $block['content']['library'];
					}
					$elementOptions['layout'] = isset($block['content']['layout']) ? $block['content']['layout']:'blank';
					$elementOptions['template'] = $block['content']['template'];

					$appConfig = Libraries::get(true);
					$paths = array(
						'layout' => array(
							'{:library}/views/layouts/{:layout}.{:type}.php',
							$appConfig['path'] . '/views/layouts/{:layout}.{:type}.php'
						),
						'template' => array(
							$appConfig['path'] . '/views/_libraries/{:library}/blocks/{:template}.{:type}.php',
							'{:library}/views/blocks/{:template}.{:type}.php',
							$appConfig['path'] . '/views/blocks/{:template}.{:type}.php'
						)
					);

					$View = new View(array('paths' => $paths));
					$string .= $View->render('all', $data, $elementOptions);
				}
			}

			if(isset($block['options']['wrapperId'])) {
				$string .= "\n\t" . '</div>';
			}
		}
		if($options['wrapperId']) {
			$string .= '</div>';
		}
		$string .= "\n";

		return $string;
	}
}
?>