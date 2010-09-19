<?php
/**
 * Jailson for CakePHP
 *   Access Control Plugin
 * 
 * @category CakePHP
 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
 * @package plugins.jailson
 * @subpackage plugins.jailson.components
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @link http://github.com/m3nt0r/cakephp-jailson Repository/Docs
 * @copyright (c) 2010, Kjell Bublitz (http://cakealot.com)
 */
/**
 * Jailson - Acl Auth Component
 * 
 * @category CakePHP
 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
 * @package plugins.jailson
 * @subpackage plugins.jailson.components
 */
class AclAuthComponent extends Object {
	
	/**
	 * AuthComponent reference
	 */
	protected $_Auth;
	
	/**
	 * Controller reference
	 */
	protected $_Controller;
	
	/**
	 * Deny rules
	 */
	public $deny = array();
	
	/**
	 * Allow rules
	 */
	public $allow = array();
	
	/**
	 * Keep parent settings
	 */
	private $__settings = array();
	
	/**
	 * Name of CakePHP AuthComponent 
	 *   In case you subclassed it..
	 * 
	 * @var string
	 */
	public $authClass = 'Auth';
	
	/**
	 * Initialize the ACL Component
	 * 
	 * - Import and configure the AuthComponent or throw error if missing
	 * - Import the user model from Auth
	 * - Normalize deny/allow $settings
	 * - Store reference to current controller
	 */
	public function initialize(&$controller, $settings) {
		$this->_set($settings);
		$this->__settings = $settings;
		
		// Import controller
		$this->_Controller =& $controller;
		
		// Import and modify AuthComponent
		if (isset($controller->{$this->authClass})) {
			$this->_Auth =& $controller->{$this->authClass};
			$this->_Auth->authorize = 'object';
			$this->_Auth->object = $this;
		} else {
			trigger_error(__("Could not find {$this->authClass}Component. Please include {$this->authClass} in Controller::\$components.", true), E_USER_WARNING);
		}
	}
	
	/**
	 * Called by AuthComponent
	 * 
	 * @param 	mixed 	$user 		The user to check the authorization of
	 * @param 	string 	$controller	Name of the controller to check
	 * @param 	string	$action 	Name of the action to check
	 * 
	 * @return boolean True if $user is authorized, otherwise false
	 */
	public function isAuthorized($user, $controller, $action) {
		
		$userModel = $this->_Auth->getModel();
		$userModel->id = $user[$this->_Auth->userModel]['id'];
		
		$currentPath = $controller .'/'. $action;
		
		if (!empty($this->__settings['deny'])) {
			$this->deny = $this->_normalizePermissions($this->deny, $this->__settings['deny']);
		}
		if (!empty($this->__settings['allow'])) {
			$this->allow = $this->_normalizePermissions($this->allow, $this->__settings['allow']);
		}
		
		// process denied
		if (array_key_exists($currentPath, $this->deny)) {
			$required = $this->deny[$currentPath];
			if (in_array('*', $required)) {
				return false; // deny any role
			}
			return $this->_dispatch($userModel, 'isNot', $required);
		}
		
		// process allowed
		if (array_key_exists($currentPath, $this->allow)) {
			$required = $this->allow[$currentPath];
			if (in_array('*', $required)) {
				return true; // allow any role
			}
			return $this->_dispatch($userModel, 'is', $required);
		}
		
		// process controller wildcard
		if (array_key_exists("{$controller}/*", $this->allow)) {
			return true; // the entire controller is allowed
		}
		if (array_key_exists("{$controller}/*", $this->deny)) {
			return false; // the entire controller is denied
		}
		
		return true; // not controlled by acl
	}
	
	/**
	 * Call Jailson behavior method with role config
	 * 
	 * @param	object	$userModel	Model using the Inmate behavior
	 * @param	string	$method		Name of the method
	 * @param	array	$required	Role config array
	 * 
	 * @return	boolean	casted bool result of $method
	 * 
	 * @todo add support for sentences in AND-style role matching (array('foo' => array('bar'), 'baz', 'etc'))
	 */
	protected function _dispatch($userModel, $method, $required) {
		$result = false;
		
		if (!is_array($userModel->actsAs) || (!array_key_exists('Jailson.Inmate', $userModel->actsAs) && !in_array('Jailson.Inmate', $userModel->actsAs))) {
			trigger_error(__("Looks like your userModel is missing the behavior. Please include 'Jailson.Inmate' in {$userModel->name}::\$actsAs.", true), E_USER_WARNING);
		}
		
		foreach ($required as $role => $rule) {
			if (is_array($rule) && !is_numeric($role)) {					
				$arguments = array_merge(array($role), $rule);
			} else {
				$arguments = array($rule);
			}
			
			$result = call_user_func_array(array($userModel, $method), $arguments);
			
			if ($result) {
				break; // stop on first match
			}
		}
		return (bool) $result;
	}
	
	/**
	 * Convert any style of allow/deny keys to the standard controller/action format.
	 * 
	 * @param 	array	$array;
	 * @return	array
	 */
	protected function _normalizePermissions($array, $targetArray = array()) {
		$normalized = array();
		foreach ($array as $controllerAction => $requiredRoles) {
			if ($requiredRoles == '*') {
				$controllerActionSplit = array($this->_Controller->name, '*');
				$requiredRoles = array();
			} else {
				$controllerActionSplit = explode('/', $controllerAction);
			}
			
			if (isset($controllerActionSplit[1])) {
				$action = $controllerActionSplit[1];
				$controller = $controllerActionSplit[0];
			} else {
				$controller = $this->_Controller->name;
				$action = $controllerActionSplit[0];
			}
			
			// support inheritance
			if (isset($targetArray["{$controller}/{$action}"])) {
				$previousRoles = $targetArray["{$controller}/{$action}"];
				$requiredRoles = array_unique(array_merge_recursive($previousRoles, $requiredRoles));
			}
			
			$normalized["{$controller}/{$action}"] = $requiredRoles; 
		}
		return $normalized;
	}
	
}