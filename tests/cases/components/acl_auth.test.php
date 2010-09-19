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
App::import('Component', array('Auth', 'Jailson.AclAuth'));
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
/**
* TestAclAuthComponent class
*
* @package       cake
* @subpackage    cake.tests.cases.libs.controller.components
*/
class TestAclAuthComponent extends AclAuthComponent {
	
}


/**
* TestAuthComponent class
*
* @package       cake
* @subpackage    cake.tests.cases.libs.controller.components
*/
class TestAuthComponent extends AuthComponent {

	var $loginAction = 'auth_test/login';

/**
 * testStop property
 *
 * @var bool false
 * @access public
 */
	var $testStop = false;

/**
 * Sets default login state
 *
 * @var bool true
 * @access protected
 */
	var $_loggedIn = true;

/**
 * stop method
 *
 * @access public
 * @return void
 */
	function _stop() {
		$this->testStop = true;
	}
}


/**
* AuthTestController class
*
* @package       cake
* @subpackage    cake.tests.cases.libs.controller.components
*/
class AuthTestController extends Controller {

/**
 * name property
 *
 * @var string 'AuthTest'
 * @access public
 */
	var $name = 'AuthTest';

/**
 * uses property
 *
 * @var array
 * @access public
 */
	var $uses = array('TestUser');

/**
 * components property
 *
 * @var array
 * @access public
 */
	var $components = array(
		'Session', 'TestAuth', 
		'TestAclAuth' => array(
			'authClass' => 'TestAuth'
		)
	);

/**
 * testUrl property
 *
 * @var mixed null
 * @access public
 */
	var $testUrl = null;

/**
 * construct method
 *
 * @access private
 * @return void
 */
	function __construct() {
		$this->params = Router::parse('/auth_test');
		Router::setRequestInfo(array($this->params, array(
			'base' => null, 
			'here' => '/auth_test', 
			'webroot' => '/', 
			'passedArgs' => array(), 
			'argSeparator' => ':',
			 'namedArgs' => array()
		)));
		parent::__construct();
	}

/**
 * beforeFilter method
 *
 * @access public
 * @return void
 */
	function beforeFilter() {
		$this->TestAuth->userModel = 'TestUser';
	}

/**
 * login method
 *
 * @access public
 * @return void
 */
	function login() {
	}

/**
 * admin_login method
 *
 * @access public
 * @return void
 */
	function admin_login() {
	}

/**
 * logout method
 *
 * @access public
 * @return void
 */
	function logout() {
		// $this->redirect($this->Auth->logout());
	}

/**
 * add method
 *
 * @access public
 * @return void
 */
	function add() {
		echo "add";
	}

/**
 * edit method
 *
 * @access public
 * @return void
 */
	function edit() {
		echo "edit";
	}
	
/**
 * add method
 *
 * @access public
 * @return void
 */
	function camelCase() {
		echo "camelCase";
	}

/**
 * redirect method
 *
 * @param mixed $url
 * @param mixed $status
 * @param mixed $exit
 * @access public
 * @return void
 */
	function redirect($url, $status = null, $exit = true) {
		$this->testUrl = Router::url($url);
		return false;
	}

/**
 * isAuthorized method
 *
 * @access public
 * @return void
 */
	function isAuthorized() {
		if (isset($this->params['testControllerAuth'])) {
			return false;
		}
		return true;
	}

/**
 * Mock delete method
 *
 * @param mixed $url
 * @param mixed $status
 * @param mixed $exit
 * @access public
 * @return void
 */
	function delete($id = null) {
		if ($this->TestAuth->testStop !== true && $id !== null) {
			echo 'Deleted Record: ' . var_export($id, true);
		}
	}
}

class AclAuthTest extends CakeTestCase {
	public $fixtures = array(
		'plugin.jailson.user',
		'plugin.jailson.inmate'
	);
	
	public function startTest() {
		$this->_server = $_SERVER;
		$this->_env = $_ENV;
		
		$this->_securitySalt = Configure::read('Security.salt');
		Configure::write('Security.salt', 'JfIxfs2guVoUubWDYhG93b0qyJfIxfs2guwvniR2G0FgaC9mi');
		
		$this->Controller =& new AuthTestController();
		$this->Controller->Component->init($this->Controller);
		$this->Controller->Component->initialize($this->Controller);
		$this->Controller->beforeFilter();
		
		ClassRegistry::addObject('view', new View($this->Controller));

		$this->Controller->Session->delete('Auth');
		$this->Controller->Session->delete('Message.auth');

		Router::reload();

		$this->initialized = true;
	}
	
	public function endTest() {
		$_SERVER = $this->_server;
		$_ENV = $this->_env;
		Configure::write('Security.salt', $this->_securitySalt);

		$this->Controller->Session->delete('Auth');
		$this->Controller->Session->delete('Message.auth');
		ClassRegistry::flush();
		unset($this->Controller);
	}
	
	public function testNoAuth() {
		$this->assertFalse($this->Controller->TestAuth->isAuthorized());
	}
	
	
	function __login() {
		$this->TestUser = new TestUser();
		$authUser = $this->TestUser->read(null, 1); // get John
		
		$this->Controller->data['TestUser']['username'] = $authUser['TestUser']['username'];
		$this->Controller->data['TestUser']['password'] = 'test';

		$this->Controller->params = Router::parse('auth_test/login');
		$this->Controller->params['url']['url'] = 'auth_test/login';

		$this->Controller->TestAuth->initialize($this->Controller);
		$this->Controller->TestAuth->startup($this->Controller);
		
		// test valid login 
		$result = $this->Controller->TestAuth->login($this->Controller->data);
		$this->assertTrue($result); 
	}
	
	/**
	* requires __login();
	*/
	function __testStartupConfig($url, $config = array()) {
		$this->Controller->params = Router::parse($url);
		$this->Controller->params['url']['url'] = Router::normalize($url);
		$this->Controller->TestAuth->initialize($this->Controller);
		$this->Controller->TestAclAuth->initialize($this->Controller, $config);
		return $this->Controller->TestAuth->startup($this->Controller);
	}
	
	
	function testLogin() {
		// test valid login 
		$this->__login();
		
		// test session content
		$result = $this->Controller->TestAuth->user();
		$expected = array(
			'TestUser' => array(
				'id' => 1,
				'username' => 'John'
			)
		);
		$this->assertEqual($result, $expected);
	}
	
	# =================================================
	# Default Behavior
	# =================================================
	
	// Behavior: User can access the page since no special permission are in place
	function testEmpty() {
		$this->__login();
		
		$url = '/auth_test/delete';
		$result = $this->__testStartupConfig($url);
		$this->assertTrue($result);
	}
	
	# =================================================
	# Allow/Deny *
	# =================================================
	
	// Behavior: User is denied from accessing the page.
	function testDenyAll() {
		$this->__login();
		
		$url = '/auth_test/delete';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array('*')
		));
		$this->assertFalse($result);
		
		$url = '/auth_test/add';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array('*')
		));
		$this->assertFalse($result);
	}
	
	// Behavior: User can access the page as everybody is allowed
	function testAllowAll() {
		$this->__login();
		
		$url = '/auth_test/delete';
		$result = $this->__testStartupConfig($url, array(
			'allow' => array('*')
		));
		$this->assertTrue($result);
		
		$url = '/auth_test/add';
		$result = $this->__testStartupConfig($url, array(
			'allow' => array('*')
		));
		$this->assertTrue($result);
	} 
	
	# =================================================
	# Allow/Deny group
	# =================================================
	
	// Behavior: User needs to be in a group to access the page
	function testAllowForGroup() {
		$this->__login();
		
		$url = '/auth_test/add';
		$result = $this->__testStartupConfig($url, array(
			'allow' => array(
				'add' => array('member')  // implies a "deny-everybody else"
			)
		));
		$this->assertFalse($result); 
		// false - not in the group
	}
	
	// Behavior: User needs to be in a group to access the page
	function testDenyForGroup() {
		$this->__login();
		
		$url = '/auth_test/delete';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array(
				'delete' => array('member') // only deny access to this group
			)
		));
		$this->assertTrue($result); 
		// true - not in the group
	}
	
	# =================================================
	# Starting matching tests with fixtures
	# =================================================
	 
	function testMatch() {
		$this->__login();
		
		// check if authed user has 'singer' role in DB
		$testUser = new TestUser();
		$testUser->id = $this->Controller->TestAuth->user('id');
		$roles = $testUser->roles(true);
		$this->assertTrue(in_array('singer', $roles));
		
		// allow 'singer'
		$url = '/auth_test/add';
		$result = $this->__testStartupConfig($url, array(
			'allow' => array(
				'add' => array('singer')  
			)
		));
		$this->assertTrue($result); 
		
		// deny 'singer'
		$url = '/auth_test/add';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array(
				'add' => array('singer')  
			)
		));
		$this->assertFalse($result);
	} 
	
	function testMatchSentence() {
		$this->__login();
		
		// check if authed user has 'singer' role in DB
		$testUser = new TestUser();
		$testUser->id = $this->Controller->TestAuth->user('id');
		$result = $testUser->is('singer', 'pianist');
		$this->assertTrue($result);
		
		// allow 'singer'
		$url = '/auth_test/add';
		$result = $this->__testStartupConfig($url, array(
			'allow' => array(
				'add' => array(
					'singer' => array('pianist')
				)
			)
		));
		$this->assertTrue($result); 
		
		// deny 'singer'
		$url = '/auth_test/add';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array(
				'add' => array(
					'singer' => array('pianist')
				)
			)
		));
		$this->assertFalse($result);
	}
	
	function testMatchMultiple() {
		$this->__login();
		
		// check if authed user has 'singer' role in DB
		$testUser = new TestUser();
		$testUser->id = $this->Controller->TestAuth->user('id');
		$testUser->is('writer', true); // add writer
		$result = $testUser->is(array('singer','writer'));
		$this->assertTrue($result);
		
		// allow 'singer'
		$url = '/auth_test/add';
		$result = $this->__testStartupConfig($url, array(
			'allow' => array(
				'add' => array(
					array('singer','writer')
				)
			)
		));
		$this->assertTrue($result); 
		
		// deny 'singer'
		$url = '/auth_test/add';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array(
				'add' => array(
					array('singer','writer')
				)
			)
		));
		$this->assertFalse($result);
	}
	
	function testMatchMixed() {
		$this->__login();
		
		// check if authed user has 'singer' role in DB
		$testUser = new TestUser();
		$testUser->id = $this->Controller->TestAuth->user('id');
		$testUser->is('writer', true); // add writer
		$result = $testUser->is(array('singer','writer'));
		$this->assertTrue($result);
		
		// allow 'singer'
		$url = '/auth_test/add';
		$result = $this->__testStartupConfig($url, array(
			'allow' => array(
				'add' => array(
					'member',
					'member' => 'editor',
					array('singer','writer')
				)
			)
		));
		$this->assertTrue($result); 
		
		// deny 'singer'
		$url = '/auth_test/add';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array(
				'add' => array(
					'member',
					'member' => 'editor',
					array('singer','writer')
				)
			)
		));
		$this->assertFalse($result);
	}
	
	# =================================================
	# Testing combinations
	# =================================================
	
	function testMatch_AllowOne_DenyOne() {
		$this->__login();
		
		// check if authed user has 'singer' role in DB
		$testUser = new TestUser();
		$testUser->id = $this->Controller->TestAuth->user('id');
		$roles = $testUser->roles(true);
		$this->assertTrue(in_array('singer', $roles));
		
		// allow 'singer', but deny 'something'
		$url = '/auth_test/add';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array(
				'add' => array('something')
			),
			'allow' => array(
				'add' => array('singer')  
			)
		));
		$this->assertTrue($result); 
		
		// deny 'singer', but allow 'something'
		$url = '/auth_test/add';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array(
				'add' => array('singer')  
			),
			'allow' => array(
				'add' => array('something')  
			)
		));
		$this->assertFalse($result);
	}
	
	
	function testMatch_RuleOne_xOne() {
		$this->__login();
		
		// check if authed user has 'singer' role in DB
		$testUser = new TestUser();
		$testUser->id = $this->Controller->TestAuth->user('id');
		$roles = $testUser->roles(true);
		$this->assertTrue(in_array('singer', $roles));
		
		// allow 'singer', but deny 'something'
		$url = '/auth_test/add';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array(
				'add' => array('singer')
			),
			'allow' => array(
				'add' => array('*')  
			)
		));
		$this->assertFalse($result); 
		
		// deny 'everything', but allow 'singer'
		$url = '/auth_test/add';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array(
				'add' => array('*')  
			),
			'allow' => array(
				'add' => array('singer')  
			)
		));
		$this->assertTrue($result);
	}
	
	
	function testMatch_DoubleFalse() {
		$this->__login();
		
		// check if authed user has 'singer' role in DB
		$testUser = new TestUser();
		$testUser->id = $this->Controller->TestAuth->user('id');
		$roles = $testUser->roles(true);
		$this->assertTrue(in_array('singer', $roles));
		
		// deny 'idontexist', allow 'idontexist'
		$url = '/auth_test/add';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array(
				'add' => array('idontexist') // false
			),
			'allow' => array(
				'add' => array('idontexist') // false
			)
		));
		$this->assertFalse($result);
	}
	
	# =================================================
	# Testing more complex rules
	# =================================================
	
	function testMatch_MultipleActions() {
		$this->__login();
		
		// check if authed user has 'singer' role in DB
		$testUser = new TestUser();
		$testUser->id = $this->Controller->TestAuth->user('id');
		$roles = $testUser->roles(true);
		$this->assertTrue(in_array('singer', $roles));
		
		// deny some actions to members. 
		// since im not a member, should be true
		$url = '/auth_test/add';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array(
				'add' => array('member'), // false
				'edit' => array('member'), // false
				'delete' => array('member'), // false
			)
		));
		$this->assertTrue($result);
		
		// different action
		$url = '/auth_test/edit';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array(
				'add' => array('member'), // false
				'edit' => array('member'), // false
				'delete' => array('member'), // false
			)
		));
		$this->assertTrue($result);
		
		// allow some actions to members. 
		// since im not a member, should be false
		$url = '/auth_test/delete';
		$result = $this->__testStartupConfig($url, array(
			'allow' => array(
				'add' => array('member'), // false
				'edit' => array('member'), // false
				'delete' => array('member'), // false
			)
		));
		$this->assertFalse($result);
		
		
		$url = '/auth_test/delete';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array(
				'add' => array('member'), // false
				'edit' => array('member'), // false
				'delete' => array('member'), // false
			),
			'allow' => array(
				'add' => array('member'), // false
				'edit' => array('member'), // false
				'delete' => array('member'), // false
			)
			// makes no sense, but that shouldn't hurt the processing.
		));
		$this->assertFalse($result);
		
		
		$url = '/auth_test/delete';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array(
				'add' => array('member'), // false
				'edit' => array('member'), // false
				'delete' => array('member'), // false
			),
			'allow' => array(
				'add' => array('member'), // false
				'edit' => array('singer' => 'pianist'), // true
				'delete' => array('singer'), // true
			)
		));
		$this->assertTrue($result);
		
		// bunch of deny, allows. using "allow edit" for test
		$url = '/auth_test/edit';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array(
				'add' => array('member'), // false
				'edit' => array('member'), // false
				'delete' => array('member'), // false
			),
			'allow' => array(
				'add' => array('member'), // false
				'edit' => array('singer' => 'pianist'), // true
				'delete' => array('singer'), // true
			)
		));
		$this->assertTrue($result);
		
		// bunch of deny, allows. using "allow edit" for test
		$url = '/auth_test/delete';
		$result = $this->__testStartupConfig($url, array(
			'deny' => array('*'),
			'allow' => array(
				'add' => array('member'), // false
				'edit' => array('singer' => 'pianist'), // true
				'delete' => array('singer'), // true
			)
		));
		$this->assertTrue($result);
	}
	
	
}
?>