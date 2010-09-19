<?php  
/**
 * Jailson for CakePHP
 *   Access Control Plugin
 * 
 * @category CakePHP
 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
 * @package plugins.jailson
 * @subpackage plugins.jailson.tests.fixtures
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @link http://github.com/m3nt0r/cakephp-jailson Repository/Docs
 * @copyright (c) 2010, Kjell Bublitz (http://cakealot.com)
 */
class UserFixture extends CakeTestFixture { 
	public $name = 'User'; 
	public $table = 'test_users';

	public $fields = array( 
		'id' => array('type' => 'integer', 'key' => 'primary'), 
		'username' => array('type' => 'string', 'length' => 255, 'null' => false),
		'password' => array('type' => 'string', 'length' => 255, 'null' => false)
	); 
	public $records = array( 
		array ('id' => 1, 'username' => 'John', 'password' => '40b722104a7c1e78e96fb7bd56d9ef2d4856691c'), 
		array ('id' => 2, 'username' => 'Paul', 'password' => '40b722104a7c1e78e96fb7bd56d9ef2d4856691c'), 
		array ('id' => 3, 'username' => 'George', 'password' => '40b722104a7c1e78e96fb7bd56d9ef2d4856691c'),
		array ('id' => 4, 'username' => 'Ringo', 'password' => '40b722104a7c1e78e96fb7bd56d9ef2d4856691c'),
	); 
	
	// password = 'test';
} 
?>