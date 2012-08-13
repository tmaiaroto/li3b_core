<?php

namespace li3b_core\models;

class Session extends \lithium\data\Model {
	
	protected $_meta = array(
		'connection' => 'li3b_mongodb',
		'source' => 'li3b.sessions'
	);
	
}
?>