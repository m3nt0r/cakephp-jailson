<?php
/**
 * Jailson for CakePHP
 *   Access Control Plugin
 * 
 * @category CakePHP
 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
 * @package plugins.jailson
 * @subpackage plugins.jailson.models
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @link http://github.com/m3nt0r/cakephp-jailson Repository/Docs
 */
class InmateFixture extends CakeTestFixture { 
	public $name = 'Inmate'; 
	public $table = 'test_inmates';
		
	public $fields = array( 
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
		'key' => array('type' => 'string', 'null' => true, 'default' => NULL, 'key' => 'index'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'key_idx' => array('column' => 'key', 'unique' => 1)
		)
	); 
	public $records = array( 
		array ('id' => 1, 'key' => 'Testuser/1/singer'), 
		array ('id' => 2, 'key' => 'Testuser/1/singer/pianist'),
		array ('id' => 3, 'key' => 'Testuser/2/bass'), 
		array ('id' => 4, 'key' => 'Testuser/2/bass/guitar'),
		array ('id' => 5, 'key' => 'Testuser/3/lead'),
		array ('id' => 6, 'key' => 'Testuser/3/lead/guitar'),
		array ('id' => 7, 'key' => 'Testuser/4/drummer'),
		array ('id' => 8, 'key' => 'Testuser/4/drummer/percussions'),
	); 
} 
?>