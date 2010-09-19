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
 */
class UserFixture extends CakeTestFixture { 
	public $name = 'User'; 
	public $table = 'test_users';

	public $fields = array( 
		'id' => array('type' => 'integer', 'key' => 'primary'), 
		'name' => array('type' => 'string', 'length' => 255, 'null' => false)
	); 
	public $records = array( 
		array ('id' => 1, 'name' => 'John'), 
		array ('id' => 2, 'name' => 'Paul'), 
		array ('id' => 3, 'name' => 'George'),
		array ('id' => 4, 'name' => 'Ringo'),
	); 
} 
?>