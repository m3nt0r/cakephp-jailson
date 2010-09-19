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

App::import('Model','Jailson.Inmate');  
App::import('Behavior','Jailson.Inmate');  

class TestInmate extends Inmate {
	public $useTable = 'test_inmates';
	public $useDbConfig = "test_suite";
	public $cacheSources = false;
	public $hasAndBelongsToMany = array();
	public $belongsTo = array();
	public $hasOne = array();
	public $hasMany = array();
}

class TestUser extends AppModel {
	public $useTable = 'test_users';
	public $useDbConfig = "test_suite";
	public $cacheSources = false;
	public $actsAs = array(
		'Jailson.Inmate' => array(
			'inmateModel' => 'TestInmate',
			'disableCache' => true
		)
	);
	public $hasAndBelongsToMany = array();
	public $belongsTo = array();
	public $hasOne = array();
	public $hasMany = array();
}

class TestProject extends AppModel {
	public $useTable = 'test_projects';
	public $useDbConfig = "test_suite";
	public $cacheSources = false;
	public $hasAndBelongsToMany = array();
	public $belongsTo = array();
	public $hasOne = array();
	public $hasMany = array();
}

class UserTestCase extends CakeTestCase { 
	public $fixtures = array(
		'plugin.jailson.inmate',
		'plugin.jailson.project',
		'plugin.jailson.user'
	);
	public $User;
	public $Project;
	
	public function startTest() {
		$this->User = new TestUser();
		$this->Project = new TestProject();
	}
	public function endTest() {
		unset($this->User);
	}
	
	# =================================================
	# Inmate::lockAs()
	# =================================================
	
	public function testLocking() {
		
		// required
		$this->User->id = 1;
		
		// add user to group
		$result = $this->User->lockAs('beatle');
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'beatle'
			)
		);
		$this->assertEqual($result, $expected);
		
		// add existing
		$result = $this->User->lockAs('beatle');
		$expected = array( 
		); 
		$this->assertEqual($result, $expected);
	}
	
	public function testObjectLocking() {
		
		// required
		$this->User->id = 1;
		$this->Project->id = 1;
		
		// add user to object
		$result = $this->User->lockAs('member', $this->Project);
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'member',
				'subject' => 'TestProject',
				'subject_id' => '1'
			)
		);
		$this->assertEqual($result, $expected);
		
		
		// add existing
		$result = $this->User->lockAs('member', $this->Project);
		$expected = array( 
		);
		$this->assertEqual($result, $expected);
		
		// add multiple roles to object
		$result = $this->User->lockAs(array('musician', 'artist'), $this->Project);
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'musician',
				'subject' => 'TestProject',
				'subject_id' => '1'
			),array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'artist',
				'subject' => 'TestProject',
				'subject_id' => '1'
			)
		);
		$this->assertEqual($result, $expected);
		
		// add without id on object
		$this->Project->id = null;
		$result = $this->User->lockAs('member', $this->Project);
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'member',
				'subject' => 'TestProject'
			)
		);
		$this->assertEqual($result, $expected);
		
		// That the missing id does not throw a error is okay for now.
		// The system can check for it anyway. But.. there are plans 
		// to make good use of this storage. 
	}
	
	public function testStringLocking() {
		
		// required
		$this->User->id = 1;
		
		// add user to group
		$result = $this->User->lockAs('awesome', 'hacker');
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'awesome',
				'subject' => 'hacker'
			)
		);
		$this->assertEqual($result, $expected);
	}
	
	public function testSemanticLocking() {
		
		// required
		$this->User->id = 1;
		
		// the _in suffix
		$result = $this->User->lockAs('awesome_in', 'hacking');
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'awesome',
				'subject' => 'hacking'
			)
		);
		$this->assertEqual($result, $expected);
		
		// weird _by syntax ftw
		$result = $this->User->lockAs('A_w-e^s+_me_by', 'design');
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'A_w-e^s+_me',
				'subject' => 'design'
			)
		);
		$this->assertEqual($result, $expected);
		
		// lets just test them all...
		$roles = array(
			'master_of',
			'toocool_for',
			'gettin_in',
			'passing_by',
			'rocking_on',
			'winning_at',
			'jailson_rawks'
		);
		$result = $this->User->lockAs($roles);
		
		// huge array incoming... run!!
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'master'
			),array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'toocool'
			),array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'gettin'
			),array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'passing'
			),array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'rocking'
			),array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'winning'
			),array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'jailson_rawks'
			)
		);
		
		$this->assertEqual($result, $expected);
	}
	
	
	# =================================================
	# Inmate::has()
	# =================================================
	
	public function testHas() {
		
		$this->User->id = 1;
		
		$result = $this->User->has('singer');
		$this->assertTrue($result);
		
		$result = $this->User->has('cheezburger');
		$this->assertFalse($result);
		
		$result = $this->User->has('singer', 'pianist');
		$this->assertTrue($result);
		
		$result = $this->User->has(array('singer'));
		$this->assertTrue($result);
		
		$result = $this->User->has(array('singer'), 'pianist');
		$this->assertTrue($result);
		
		// not writer
		$result = $this->User->has(array('singer', 'writer'));
		$this->assertFalse($result);
		
		// lock as writer
		$result = $this->User->lockAs('writer');
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'writer'
			)
		);
		$this->assertEqual($result, $expected);
		
		// is also in writer
		$result = $this->User->has(array('singer', 'writer'));
		$this->assertTrue($result);
	}
	
	public function testHasObject() {
		
		$this->User->id = 1;
		$this->Project->id = 1;
		
		// add user to object
		$result = $this->User->lockAs('member', $this->Project);
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'member',
				'subject' => 'TestProject',
				'subject_id' => '1'
			)
		);
		$this->assertEqual($result, $expected);
		
		// test object relationship
		$result = $this->User->has('member', $this->Project);
		$this->assertTrue($result);
		
		
		// test object relationship, different object
		$this->Project->id = 2;
		$result = $this->User->has('member', $this->Project);
		$this->assertFalse($result);
		
		// test object relationship, anon object
		$this->Project->id = null;
		$result = $this->User->has('member', $this->Project);
		$this->assertFalse($result);
		
		// add another role to same object as the first test
		$this->Project->id = 1;
		$result = $this->User->lockAs('owner', $this->Project);
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'owner',
				'subject' => 'TestProject',
				'subject_id' => '1'
			)
		);
		$this->assertEqual($result, $expected);	
		
		// test multiple roles on object
		
		$result = $this->User->has(array('owner'), $this->Project);
		$this->assertTrue($result);
		
		$result = $this->User->has(array('member','owner'), $this->Project);
		$this->assertTrue($result);
		
		$result = $this->User->has(array('member','owner', 'solo'), $this->Project);
		$this->assertFalse($result);
	}
	
	
	# =================================================
	# Inmate::is()
	# =================================================
	
	public function testIs() {
		
		$this->User->id = 1;
		
		// single
		$result = $this->User->is('singer');
		$this->assertTrue($result);
		
		// with subject
		$result = $this->User->is('singer', 'pianist');
		$this->assertTrue($result);
		
		// not found
		$result = $this->User->is('anon');
		$this->assertFalse($result);
		
		// create switch
		$result = $this->User->is('anon', true);
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'anon',
			)
		);
		$this->assertEqual($result, $expected);
		
		// was created, same query yields true now
		$result = $this->User->is('anon');
		$this->assertTrue($result);
		
		// test multiple
		$result = $this->User->is(array('anon', 'singer'));
		$this->assertTrue($result);
		
		// test multiple not found
		$result = $this->User->is(array('not', 'found'));
		$this->assertFalse($result);
		
		// create multiple with switch
		$result = $this->User->is(array('not', 'found'), true);
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'not',
			),array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'found',
			)
		);
		$this->assertEqual($result, $expected);
		
		$result = $this->User->is(array('not', 'found'));
		$this->assertTrue($result);
	}
	
	public function testObjectIs() {
		
		$this->User->id = 1;
		$this->Project->id = 1;
		
		// add as member to project
		$result = $this->User->lockAs('member', $this->Project);
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'member',
				'subject' => 'TestProject',
				'subject_id' => '1'
			)
		);
		$this->assertEqual($result, $expected);
		
		// single
		$result = $this->User->is('member', $this->Project);
		$this->assertTrue($result);
		
		// wrong role
		$result = $this->User->is('singer', $this->Project);
		$this->assertFalse($result);
		
		// add as singer to project
		$result = $this->User->is('singer', $this->Project, true);
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'singer',
				'subject' => 'TestProject',
				'subject_id' => '1'
			)
		);
		$this->assertEqual($result, $expected);
		
		// test multiple
		$result = $this->User->is(array('member', 'singer'), $this->Project);
		$this->assertTrue($result);
	}
	
	# =================================================
	# Inmate::isNot()
	# =================================================
	
	public function testIsNot() {
		
		$this->User->id = 1;
		
		
		
		// single
		$result = $this->User->isNot('singer');
		$this->assertFalse($result);
		
		// with subject
		$result = $this->User->isNot('singer', 'pianist');
		$this->assertFalse($result);
		
		// not found
		$result = $this->User->isNot('anon');
		$this->assertTrue($result);
		
		// delete switch
		$result = $this->User->isNot('singer', true);
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'singer',
			)
		);
		$this->assertEqual($result, $expected);
		
		// was deleted, same query yields true now
		$result = $this->User->isNot('singer');
		$this->assertTrue($result);
		
		// test multiple
		$result = $this->User->isNot(array('anon', 'singer'));
		$this->assertTrue($result);
		
		// test multiple not found
		$result = $this->User->isNot(array('not', 'found'));
		$this->assertTrue($result);
		
		// create multiple with switch
		$result = $this->User->is(array('not', 'found'), true);
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'not',
			),array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'found',
			)
		);
		$this->assertEqual($result, $expected);
		
		// since we created them, isNot is now false
		$result = $this->User->isNot(array('not', 'found'));
		$this->assertFalse($result);
		
		// delete multiple through switch
		$result = $this->User->isNot(array('not', 'found'), true);
		
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'found',
			),array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'not',
			)
		);
		$this->assertEqual($result, $expected);
		
		// roles shouldn't match anymore.
		$result = $this->User->isNot(array('not', 'found'));
		$this->assertTrue($result);
	}
	
	public function testObjectIsNot() {
		
		$this->User->id = 1;
		$this->Project->id = 1;
		
		// add as member to project
		$result = $this->User->lockAs(array('member_in', 'singer_of'), $this->Project);
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'member',
				'subject' => 'TestProject',
				'subject_id' => '1'
			),array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'singer',
				'subject' => 'TestProject',
				'subject_id' => '1'
			)
		);
		$this->assertEqual($result, $expected);
		
		// single
		$result = $this->User->isNot('member_in', $this->Project);
		$this->assertFalse($result);
		
		// wrong role
		$result = $this->User->isNot('drummer_in', $this->Project);
		$this->assertTrue($result);
		
		// delete member from project
		$result = $this->User->isNot('member_in', $this->Project, true);
		$expected = array(
			array(
				'inmate' => 'TestUser',
				'inmate_id' => '1',
				'role' => 'member',
				'subject' => 'TestProject',
				'subject_id' => '1'
			)
		);
		$this->assertEqual($result, $expected);
		
		// test multiple (not in both)
		$result = $this->User->isNot(array('member_in', 'singer_of'), $this->Project);
		$this->assertTrue($result);
		
		// we removed from member
		$result = $this->User->isNot(array('member_in'), $this->Project);
		$this->assertTrue($result);
	}
}
?>