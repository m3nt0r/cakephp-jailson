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
	
	function testLogin() {
		
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
		$this->assertTrue($result); // logged in
		
		// test session content
		$user = $this->Controller->TestAuth->user();
		$expected = array(
			'TestUser' => array(
				'id' => 1,
				'username' => 'John'
			)
		);
		$this->assertEqual($result, $expected);
		
	}
}


?>