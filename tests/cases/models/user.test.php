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
	
}
?>