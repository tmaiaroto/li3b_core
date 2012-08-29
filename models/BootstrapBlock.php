<?php
namespace li3b_core\models;

class BootstrapBlock extends \lithium\core\StaticObject {

	/**
	 * Default/example static block content.
	 *
	 * @var array
	*/
	static $staticBlocks = array(
		'helloworld' => array(
			1 => array(
				'content' => '<p>Hello world!</p>',
				'options' => array()
			),
			2 => array(
				'content' => '<p>Hey, here\'s some more content.</p>',
				'options' => array()
			),
			3 => array(
				'content' => array('library' => 'li3b_core', 'template' => 'helloworld')
			)
		)
	);

	/**
	 * Returns a static block.
	 *
	 * Much like static menus, Static blocks are defined as arrays.
	 * The content key under each array for the block name/identifier
	 * should hold the final output.
	 *
	 * This method is filterable so the blocks can be added to or changed.
	 *
	 * How do they get ordered? They are in alphabetical order, much like
	 * statis menus. So name the keys under each block something that
	 * will put them in the desired order.
	 *
	 * Since multiple libraries may use the same block, you will also
	 * want to keep block identifiers something fairly unique. Prefixing
	 * the name with the library name is probably a good idea.
	 *
	 * Of course multiple libraries may wish to use the same exact block,
	 * but may then have an issue with ordering because they were written
	 * by two different authors (or something changed or you didn't write
	 * either library and want to re-arrange the natural order).
	 * So in this case, you can apply an ordering to any static block
	 * via a filter in your main application. Just make sure you apply
	 * the filter AFTER all the libraries have been loaded to ensure
	 * it is called last.
	 *
	 * This method will be called by a helper that will then loop
	 * the array to render the content.
	 *
	 * @param string $position The position name of the static block to return (empty value returns all blocks!)
	 * @param array $options
	 * @return array The static block(s)
	*/
	public static function staticBlock($position=null, $options=array()) {
		$defaults = array();
		$options += $defaults;
		$params = compact('position', 'options');

		$filter = function($self, $params) {
			$options = $params['options'];
			$position = $params['position'];
			$staticBlocks = array();

			/**
			 * Get a specific block or all blocks to return.
			 * Note: It's probably a bad idea to not pass a position, getting all blocks, but
			 * hey, to each their own...You may have a good reason for getting it all.
			 * Just keep in mind how big that array could be though...
			*/
			if(empty($position)) {
				$staticBlocks = $self::$staticBlocks;
			} else {
				$staticBlocks = isset($self::$staticBlocks[$params['position']]) ? $self::$staticBlocks[$params['position']]:array();
			}

			return $staticBlocks;
		};

		$blocks = static::_filter(__FUNCTION__, $params, $filter);

		// Sort the blocks by key name (but can be filtered).
		return static::order($position, $blocks);

		// return static::_filter(__FUNCTION__, $params, $filter);
	}

	/**
	 * Orders the blocks.
	 *
	 * This method is called by staticBlock() and its sole purpose
	 * is to allow the ordering of blocks for a given position.
	 * Keep in mind, this method IS filterable. This means you can filter
	 * it from somewhere, like your application's bootstrap, and have that
	 * filter apply after all filters for staticBlock(), which would have
	 * set all of the content for the blocks.
	 *
	 * This allows you to order your blocks outside this library.
	 * This means you, the developer, can control the ordering of blocks
	 * that many different libraries may have added to a position.
	 *
	 * @param  string $position The position identifier name
	 * @param  string $blocks The blocks
	 * @return array
	 */
	public static function order($position=null, $blocks=null) {
		if(empty($blocks)) {
			return array();
		}

		$params = compact('position', 'blocks');
		$filter = function($self, $params) {
			$blocks = $params['blocks'];

			ksort($blocks);

			return $blocks;
		};

		return static::_filter(__FUNCTION__, $params, $filter);
	}

}
?>