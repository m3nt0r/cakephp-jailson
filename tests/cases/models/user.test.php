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
}

if (!class_exists('TestProject')) {
	class TestProject extends AppModel {
		public $useTable = 'test_projects';
		public $useDbConfig = "test_suite";
		public $cacheSources = false;
		public $hasAndBelongsToMany = array();
		public $belongsTo = array();
		public $hasOne = array();
		public $hasMany = array();
	}
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
		unset($this->Project);
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
				'who' => 'TestUser',
				'whoId' => '1',
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
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'member',
				'what' => 'TestProject',
				'whatId' => '1'
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
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'musician',
				'what' => 'TestProject',
				'whatId' => '1'
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'artist',
				'what' => 'TestProject',
				'whatId' => '1'
			)
		);
		$this->assertEqual($result, $expected);
		
		// add without id on object
		$this->Project->id = null;
		$result = $this->User->lockAs('member', $this->Project);
		$expected = array(
			array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'member',
				'what' => 'TestProject'
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
		$result = $this->User->lockAs('ninja_at', 'cakephp');
		$expected = array(
			array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'ninja',
				'what' => 'cakephp'
			)
		);
		$this->assertEqual($result, $expected);
	}
	
	public function testSemanticLocking() {
		
		// required
		$this->User->id = 1;
		
		// the _in suffix
		$result = $this->User->lockAs('cakephp', 'coder');
		$expected = array(
			array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'cakephp',
				'what' => 'coder'
			)
		);
		$this->assertEqual($result, $expected);
		
		// weird _by syntax ftw
		$result = $this->User->lockAs('A_w-e^s+_me_by', 'design');
		$expected = array(
			array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'A_w-e^s+_me',
				'what' => 'design'
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
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'master'
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'toocool'
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'gettin'
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'passing'
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'rocking'
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'winning'
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
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
				'who' => 'TestUser',
				'whoId' => '1',
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
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'member',
				'what' => 'TestProject',
				'whatId' => '1'
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
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'owner',
				'what' => 'TestProject',
				'whatId' => '1'
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
				'who' => 'TestUser',
				'whoId' => '1',
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
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'not',
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
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
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'member',
				'what' => 'TestProject',
				'whatId' => '1'
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
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'singer',
				'what' => 'TestProject',
				'whatId' => '1'
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
				'who' => 'TestUser',
				'whoId' => '1',
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
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'not',
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
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
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'found',
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
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
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'member',
				'what' => 'TestProject',
				'whatId' => '1'
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'singer',
				'what' => 'TestProject',
				'whatId' => '1'
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
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'member',
				'what' => 'TestProject',
				'whatId' => '1'
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
	
	# =================================================
	# Inmate::did()
	# =================================================
	
	public function testAliasedIs() {
		
		$this->User->id = 1;
		
		// single
		$result = $this->User->was('singer');
		$this->assertTrue($result);
		
		// with subject
		$result = $this->User->was('singer', 'pianist');
		$this->assertTrue($result);
		
		// not found
		$result = $this->User->was('anon');
		$this->assertFalse($result);
		
		// create switch
		$result = $this->User->was('anon', true);
		$expected = array(
			array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'anon',
			)
		);
		$this->assertEqual($result, $expected);
		
		// was created, same query yields true now
		$result = $this->User->was('anon');
		$this->assertTrue($result);
		
		// test multiple
		$result = $this->User->was(array('anon', 'singer'));
		$this->assertTrue($result);
		
		// test multiple not found
		$result = $this->User->was(array('not', 'found'));
		$this->assertFalse($result);
		
		// create multiple with switch
		$result = $this->User->was(array('not', 'found'), true);
		$expected = array(
			array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'not',
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'found',
			)
		);
		$this->assertEqual($result, $expected);
		
		$result = $this->User->was(array('not', 'found'));
		$this->assertTrue($result);
	}
	
	# =================================================
	# Inmate::roles()
	# =================================================
	
	public function testRoles() {
		
		$this->User->id = 1;
		
		$result = $this->User->roles();
		$expected = array(
			'singer' => array(
				array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'singer',
				),array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'singer',
					'what' => 'pianist',
				)
			)
		);
		$this->assertEqual($result, $expected);
		
		$result = $this->User->roles( $justRoles = true );
		$expected = array(
			'singer'
		);
		$this->assertEqual($result, $expected);
	}
	
	
	# =================================================
	# Inmate::release()
	# =================================================
	
	public function testRelease() {
		
		$this->User->id = 1;
		
		// just delete /singer
		$result = $this->User->release('singer');
		$this->assertTrue($result);
		
		// so /singer/pianist is still there, right?
		$result = $this->User->is('singer', 'pianist');
		$this->assertTrue($result);
		
		// And should be the only one left
		$result = $this->User->roles();
		$expected = array(
			'singer' => array(
				array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'singer',
					'what' => 'pianist',
				)
			)
		);
		$this->assertEqual($result, $expected);
	}
	
	public function testReleaseArray() {
		
		$this->User->id = 1;
		
		// just delete /singer
		$result = $this->User->release(array('singer'));
		$this->assertTrue($result);
		
		// And should be the only one left
		$result = $this->User->roles();
		$expected = array(
			'singer' => array(
				array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'singer',
					'what' => 'pianist',
				)
			)
		);
		$this->assertEqual($result, $expected);
	}
	
	# =================================================
	# Inmate::free()
	# =================================================
	
	public function testFree() {
		
		$this->User->id = 1;
		
		// we've tested this enough.. just create some more data
		$this->User->is('singer', 'tenor', true);
		$this->User->is('singer', 'live', true);
		
		
		$this->Project->id = 1;
		$this->User->is('member', $this->Project, true);
		$this->Project->id = 2;
		$this->User->is('member', $this->Project, true);
		$this->Project->id = 3;
		$this->User->is('member', $this->Project, true);
		
		
		$result = $this->User->roles();
		$expected = array(
			'member' => array(
				array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'member',
					'what' => 'TestProject',
					'whatId' => '1'
				),array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'member',
					'what' => 'TestProject',
					'whatId' => '2'
				),array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'member',
					'what' => 'TestProject',
					'whatId' => '3'
				)
			),
			'singer' => array(
				array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'singer',
				),array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'singer',
					'what' => 'live',
				),array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'singer',
					'what' => 'pianist',
				),array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'singer',
					'what' => 'tenor',
				),
			),
		);
		$this->assertEqual($result, $expected);
		
		
		// free string subject
		$result = $this->User->free('singer', 'pianist');
		$expected = array(
			array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'singer',
				'what' => 'pianist',
			)	
		);
		$this->assertEqual($result, $expected);
		
		// is it "free"?
		$result = $this->User->isNot('singer', 'pianist');
		$this->assertTrue($result);
		
		// add it back in
		$this->User->is('singer', 'pianist', true);
		
		// free all "singer" roles
		$result = $this->User->free('singer');
		$expected = array(
			array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'singer',
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'singer',
				'what' => 'live',
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'singer',
				'what' => 'pianist',
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'singer',
				'what' => 'tenor',
			)
		);
		$this->assertEqual($result, $expected);
		
		// free all "Project" regardless of ID
		$result = $this->User->free('member', 'TestProject');
		$expected = array(
			array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'member',
				'what' => 'TestProject',
				'whatId' => '1'
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'member',
				'what' => 'TestProject',
				'whatId' => '2'
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'member',
				'what' => 'TestProject',
				'whatId' => '3'
			)
		);		
		$this->assertEqual($result, $expected);
		
		$this->Project->id = 1;
		$this->User->is('member', $this->Project, true);
		$this->Project->id = 2;
		$this->User->is('member', $this->Project, true);
		$this->Project->id = 3;
		$this->User->is('member', $this->Project, true);
		
		// free all "member" AND "Project"
		$result = $this->User->free('member');
		$expected = array(
			array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'member',
				'what' => 'TestProject',
				'whatId' => '1'
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'member',
				'what' => 'TestProject',
				'whatId' => '2'
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'member',
				'what' => 'TestProject',
				'whatId' => '3'
			)
		);		
		$this->assertEqual($result, $expected);
		
		$this->User->is('singer', true);
		$this->User->is('singer', 'pianist', true);
		$this->User->is('singer', 'tenor', true);
		$this->User->is('singer', 'live', true);
		$this->Project->id = 1;
		$this->User->is('member', $this->Project, true);
		$this->Project->id = 2;
		$this->User->is('member', $this->Project, true);
		$this->Project->id = 3;
		$this->User->is('member', $this->Project, true);
		
		$result = $this->User->roles();
		$expected = array(
			'member' => array(
				array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'member',
					'what' => 'TestProject',
					'whatId' => '1'
				),array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'member',
					'what' => 'TestProject',
					'whatId' => '2'
				),array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'member',
					'what' => 'TestProject',
					'whatId' => '3'
				)
			),
			'singer' => array(
				array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'singer',
				),array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'singer',
					'what' => 'live',
				),array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'singer',
					'what' => 'pianist',
				),array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'singer',
					'what' => 'tenor',
				),
			),
		);
		$this->assertEqual($result, $expected);
		
		// free everything
		$result = $this->User->free();
		$expected = array(
			array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'member',
				'what' => 'TestProject',
				'whatId' => '1'
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'member',
				'what' => 'TestProject',
				'whatId' => '2'
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'member',
				'what' => 'TestProject',
				'whatId' => '3'
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'singer',
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'singer',
				'what' => 'live',
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'singer',
				'what' => 'pianist',
			),array(
				'who' => 'TestUser',
				'whoId' => '1',
				'role' => 'singer',
				'what' => 'tenor',
			),
		);		
		$this->assertEqual($result, $expected);
	}
	
	# =================================================
	# Inmate::afterDelete()
	# =================================================

	public function testAfterDelete() {
		
		$this->User->id = 1;
		
		// we have 2 permissions in database
		$result = $this->User->roles();
		$expected = array(
			'singer' => array(
				array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'singer',
				),array(
					'who' => 'TestUser',
					'whoId' => '1',
					'role' => 'singer',
					'what' => 'pianist',
				)
			)
		);
		$this->assertEqual($result, $expected);
		
		// the user is deleted
		$this->User->delete(1);
		
		// query db for any data related to user-id
		$inmate = new TestInmate();
		$result = $inmate->drilldown('TestUser/1');
		$expected = array(
		);
		
		// not found = good
		$this->assertEqual($result, $expected);
	}
	
}
?>