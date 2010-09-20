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
App::import('Behavior','Jailson.Inmate');

class Solo {}
class TestObject{}
class LongTestObjectWithNoRealUse {}

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

		$solo = new Solo();
		$solo->alias = 'Solo';
		$solo->id = '4c97b774-8420-4afd-a4c0-ee535879fe73';
		
		$testObj = new TestObject();
		$testObj->alias = 'TestObject';
		$testObj->id = 123456;
		
		$weirdObj = new LongTestObjectWithNoRealUse();
		$weirdObj->alias = 'LongTestObjectWithNoRealUse';
		$weirdObj->id = '4c86c865-9ac0-460f-831c-4c1a929b39d1';
		
		

		$results[] = Storage::pack($solo, 'first');
		$results[] = Storage::pack($solo, 'first', 'one');
		$results[] = Storage::pack($solo, 'second', 'first');
		$results[] = Storage::pack($solo, 'second', 'two');
		$results[] = Storage::pack($solo, 'access', $testObj);
		$results[] = Storage::pack($solo, 'access', $weirdObj);
		

		
		
		$expected = array(
		    'Solo/4c97b774-8420-4afd-a4c0-ee535879fe73/first',
		    'Solo/4c97b774-8420-4afd-a4c0-ee535879fe73/first/one',
		    'Solo/4c97b774-8420-4afd-a4c0-ee535879fe73/second/first',
		    'Solo/4c97b774-8420-4afd-a4c0-ee535879fe73/second/two',
		    'Solo/4c97b774-8420-4afd-a4c0-ee535879fe73/access/TestObject/123456',
		    'Solo/4c97b774-8420-4afd-a4c0-ee535879fe73/access/LongTestObjectWithNoRealUse/4c86c865-9ac0-460f-831c-4c1a929b39d1',
		);
		$this->assertEqual($results, $expected);
	}
	
	# =================================================
	# InmateBehavior::_unpack()
	# =================================================
	
	public function testUnPack() {
		
		$data = array(
		    'Solo/4c97b774-8420-4afd-a4c0-ee535879fe73/first',
		    'Solo/4c97b774-8420-4afd-a4c0-ee535879fe73/first/one',
		    'Solo/4c97b774-8420-4afd-a4c0-ee535879fe73/second/first',
		    'Solo/4c97b774-8420-4afd-a4c0-ee535879fe73/second/two',
		    'Solo/4c97b774-8420-4afd-a4c0-ee535879fe73/access/TestObject/123456',
		    'Solo/4c97b774-8420-4afd-a4c0-ee535879fe73/access/LongTestObjectWithNoRealUse/4c86c865-9ac0-460f-831c-4c1a929b39d1',
		);
		
		$results = array();
		foreach ($data as $key) {
			$results[] = Storage::unpack($key);
		}
		
		$expected = array(
			array(
				'who' => 'Solo',
				'whoId' => '4c97b774-8420-4afd-a4c0-ee535879fe73',
				'role' => 'first',
			),array(
				'who' => 'Solo',
				'whoId' => '4c97b774-8420-4afd-a4c0-ee535879fe73',
				'role' => 'first',
				'what' => 'one',
			),array(
				'who' => 'Solo',
				'whoId' => '4c97b774-8420-4afd-a4c0-ee535879fe73',
				'role' => 'second',
				'what' => 'first',
			),array(
				'who' => 'Solo',
				'whoId' => '4c97b774-8420-4afd-a4c0-ee535879fe73',
				'role' => 'second',
				'what' => 'two',
			),array(
				'who' => 'Solo',
				'whoId' => '4c97b774-8420-4afd-a4c0-ee535879fe73',
				'role' => 'access',
				'what' => 'TestObject',
				'whatId' => '123456',
			),array(
				'who' => 'Solo',
				'whoId' => '4c97b774-8420-4afd-a4c0-ee535879fe73',
				'role' => 'access',
				'what' => 'LongTestObjectWithNoRealUse',
				'whatId' => '4c86c865-9ac0-460f-831c-4c1a929b39d1',
			)
		);
		
		$this->assertEqual($results, $expected);
		
		$key = '1';
		$result = Storage::unpack($key);
		$expected = array(
			'who' => '1'
		);
		$this->assertEqual($result, $expected);
		
		$key = '1/2';
		$result = Storage::unpack($key);
		$expected = array(
			'who' => '1',
			'whoId' => '2'
		);
		$this->assertEqual($result, $expected);
		
		$key = '1/2/3';
		$result = Storage::unpack($key);
		$expected = array(
			'who' => '1',
			'whoId' => '2',
			'role' => '3',
		);
		$this->assertEqual($result, $expected);
		
		$key = '1/2/3/4';
		$result = Storage::unpack($key);
		$expected = array(
			'who' => '1',
			'whoId' => '2',
			'role' => '3',
			'what' => '4',
		);
		$this->assertEqual($result, $expected);
		
		$key = '1/2/3/4/5';
		$result = Storage::unpack($key);
		$expected = array(
			'who' => '1',
			'whoId' => '2',
			'role' => '3',
			'what' => '4',
			'whatId' => '5',
		);
		$this->assertEqual($result, $expected);
		
		// no need to test > /5
		// the php compiler would complain in that case
		// since the array doesn't match _packStruct
	}
}
?>