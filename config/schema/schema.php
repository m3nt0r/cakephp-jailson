<?php 
/**
 * Jailson Schema
 * 
 *   # cake schema create jailson -plugin jailson
 * 
 */
class JailsonSchema extends CakeSchema {
	
	public $name = 'Jailson';

	/**
	 * Schema for inmates table
	 *
	 * @var array
	 * @access public
	 */
	public $inmates = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
		'key' => array('type' => 'string', 'null' => true, 'default' => NULL, 'key' => 'index'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'key_idx' => array('column' => 'key', 'unique' => 1)
		)
	);

}
?>