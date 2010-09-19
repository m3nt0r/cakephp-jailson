<?php
/**
 * Jailson for CakePHP
 *   Access Control Plugin
 * 
 * @category CakePHP
 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
 * @package plugins.jailson
 * @subpackage plugins.jailson.tests.case
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @link http://github.com/m3nt0r/cakephp-jailson Repository/Docs
 * @copyright (c) 2010, Kjell Bublitz (http://cakealot.com)
 */

App::import('Model','Jailson.Inmate');  
App::import('Behavior','Jailson.Inmate');

class TestInmateBehavior extends InmateBehavior {
	public function z_pack($model, $role, $sentence = null) {
		return parent::_pack($model, $role, $sentence);
	}
	public function z_unpack($model, $key) {
		return parent::_unpack($key);
	}
}

if (!class_exists('TestInmate')) {
	class TestInmate extends Inmate {
		public $useTable = 'test_inmates';
		public $useDbConfig = "test_suite";
		public $cacheSources = false;
		public $hasAndBelongsToMany = array();
		public $belongsTo = array();
		public $hasOne = array();
		public $hasMany = array();
	}
}

if (!class_exists('TestUser')) {
	class TestUser extends AppModel {
		public $useTable = 'test_users';
		public $useDbConfig = "test_suite";
		public $cacheSources = false;
		public $actsAs = array(
			'TestInmate' => array(
				'inmateModel' => 'TestInmate',
				'disableCache' => true
			)
		);
	}
}

class TestObject extends AppModel {
// simple i/o
	public $useTable = false;
	public $actsAs = array(
		'TestInmate' => array(
			'inmateModel' => 'TestInmate',
			'disableCache' => true
		)
	);
}
class LongTestObjectWithNoRealUse extends AppModel {
// I've seen worse
	public $useTable = false;
	public $actsAs = array(
		'TestInmate' => array(
			'inmateModel' => 'TestInmate',
			'disableCache' => true
		)
	);
}
class Solo extends AppModel {
// teh lonely
	public $useTable = false;
	public $actsAs = array(
		'TestInmate' => array(
			'inmateModel' => 'TestInmate',
			'disableCache' => true
		)
	);
}

/**
 * Inmate Behavior Tests
 *   Testing some helper methods
 */
class InmateBehaviorTest extends CakeTestCase {
	public $fixtures = array(
		'plugin.jailson.inmate',
		'plugin.jailson.project',
		'plugin.jailson.user'
	);
	
	# =================================================
	# InmateBehavior::_pack()
	# =================================================
	
	public function testPack() {
		
		$testObj = new TestObject();
		$testObj->id = 123456;
		
		$weirdObj = new LongTestObjectWithNoRealUse();
		$weirdObj->id = 987;
		
		$soloObj = new Solo();
		$soloObj->id = 1;
		$results[] = $soloObj->z_pack('first');
		$results[] = $soloObj->z_pack('first', 'one');
		$results[] = $soloObj->z_pack('second', 'first');
		$results[] = $soloObj->z_pack('second', 'two');
		$results[] = $soloObj->z_pack('access', $testObj);
		$results[] = $soloObj->z_pack('access', $weirdObj);
		
		$expected = array(
		    'Solo/1/first',
		    'Solo/1/first/one',
		    'Solo/1/second/first',
		    'Solo/1/second/two',
		    'Solo/1/access/TestObject/123456',
		    'Solo/1/access/LongTestObjectWithNoRealUse/987',
		);
		$this->assertEqual($results, $expected);
	}
	
	# =================================================
	# InmateBehavior::_unpack()
	# =================================================
	
	public function testUnPack() {
		
		$data = array(
		    'Solo/1/first',
		    'Solo/1/first/one',
		    'Solo/1/second/first',
		    'Solo/1/second/two',
		    'Solo/1/access/TestObject/123456',
		    'Solo/1/access/LongTestObjectWithNoRealUse/987',
		);
		
		$testObj = new TestObject();
		
		$results = array();
		foreach ($data as $key) {
			$results[] = $testObj->z_unpack($key);			
		}
		
		$expected = array(
			array(
				'inmate' => 'Solo',
				'inmate_id' => '1',
				'role' => 'first',
			),array(
				'inmate' => 'Solo',
				'inmate_id' => '1',
				'role' => 'first',
				'subject' => 'one',
			),array(
				'inmate' => 'Solo',
				'inmate_id' => '1',
				'role' => 'second',
				'subject' => 'first',
			),array(
				'inmate' => 'Solo',
				'inmate_id' => '1',
				'role' => 'second',
				'subject' => 'two',
			),array(
				'inmate' => 'Solo',
				'inmate_id' => '1',
				'role' => 'access',
				'subject' => 'TestObject',
				'subject_id' => '123456',
			),array(
				'inmate' => 'Solo',
				'inmate_id' => '1',
				'role' => 'access',
				'subject' => 'LongTestObjectWithNoRealUse',
				'subject_id' => '987',
			)
		);
		
		$this->assertEqual($results, $expected);
		
		$key = '1';
		$result = $testObj->z_unpack($key);
		$expected = array(
			'inmate' => '1'
		);
		$this->assertEqual($result, $expected);
		
		$key = '1/2';
		$result = $testObj->z_unpack($key);
		$expected = array(
			'inmate' => '1',
			'inmate_id' => '2'
		);
		$this->assertEqual($result, $expected);
		
		// etc..
		
		$key = '1/2/3/4/5';
		$result = $testObj->z_unpack($key);
		$expected = array(
			'inmate' => '1',
			'inmate_id' => '2',
			'role' => '3',
			'subject' => '4',
			'subject_id' => '5',
		);
		$this->assertEqual($result, $expected);
		
		// no need to test > /5
		// the php compiler would complain in that case
		// since the array doesn't match _packStruct
	}
}
?>