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
class ProjectFixture extends CakeTestFixture { 
	public $name = 'Project'; 
	public $table = 'test_projects';

	public $fields = array( 
		'id' => array('type' => 'integer', 'key' => 'primary'), 
		'name' => array('type' => 'string', 'length' => 255, 'null' => false)
	); 
	public $records = array( 
		array ('id' => 1, 'name' => 'Help!'), 
		array ('id' => 2, 'name' => 'Rubber Soul'), 
		array ('id' => 3, 'name' => 'Revolver'),
		array ('id' => 4, 'name' => 'Magical Mystery Tour'),
	); 
} 
?>