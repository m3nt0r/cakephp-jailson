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
	 * Additional Rules Source
	 * 
	 * Name of a model or a path to a ini file.
	 */
	public $loadFrom = '';
	
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
			trigger_error("Could not find {$this->authClass}Component. Please include {$this->authClass} in Controller::\$components.", E_USER_WARNING);
		}
		
		// Check loadFrom option
		if (strstr($this->loadFrom, DS)) {
			$this->_importFromIni();
		} elseif (!empty($this->loadFrom)) {
			$this->_importFromModel();
		}
	}
	
	/**
	 * Set Rules
	 *
	 * @param	array	$rules	[allow=,deny=]
	 */
	public function setRules($rules) {
		if (!empty($rules['allow'])) {
			$rules['allow'] = array_merge($this->allow, $rules['allow']);
			$this->allow = $rules['allow'];
		}
		if (!empty($rules['deny'])) {
			$rules['deny'] = array_merge($this->deny, $rules['deny']);
			$this->deny = $rules['deny'];
		}
		return $rules;
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
		
		if (!is_array($userModel->actsAs) || (!array_key_exists('Jailson.Inmate', $userModel->actsAs) && !in_array('Jailson.Inmate', $userModel->actsAs))) {
			if (basename($_SERVER['SCRIPT_NAME']) != 'test.php')
				trigger_error(__("Looks like your userModel is missing the behavior. Please include 'Jailson.Inmate' in {$userModel->name}::\$actsAs.", true), E_USER_WARNING);
		}
		
		// inheritance
		if (!empty($this->__settings['deny'])) {
			$this->deny = $this->_normalizePermissions($this->deny, $this->__settings['deny']);
		}
		if (!empty($this->__settings['allow'])) {
			$this->allow = $this->_normalizePermissions($this->allow, $this->__settings['allow']);
		}
		
		$currentPath = $controller .'/'. $action;
		
		$allowAll = false;
		$denyAll = false;
		
		$permissions = array(
			'deny' => 'null',
			'allow' => 'null'
		);

		// process controller wildcard, KISS
		if (array_key_exists("{$controller}/*", $this->allow)) {
			if (empty($this->allow[$currentPath])) $this->allow[$currentPath] = array();
			$this->allow[$currentPath] = array_merge($this->allow[$currentPath], array('*'));
		}
		if (array_key_exists("{$controller}/*", $this->deny)) {
			if (empty($this->deny[$currentPath])) $this->deny[$currentPath] = array();
			$this->deny[$currentPath] = array_merge($this->deny[$currentPath], array('*'));
		}
		
		// process allowed
		if (array_key_exists($currentPath, $this->allow)) {
			$required = $this->allow[$currentPath];
			if (in_array('*', $required)) {
				$permissions['allow'] = true;
				$allowAll = true;
			} else {
				$permissions['allow'] = $this->_assert($userModel, $required);
			}
		}

		// process denied
		if (array_key_exists($currentPath, $this->deny)) {
			$required = $this->deny[$currentPath];
			if (in_array('*', $required)) {
				$permissions['deny'] = true;
				$denyAll = true;
			} else {
				$permissions['deny'] = $this->_assert($userModel, $required);
			}
		}

		if ($permissions['allow'] === true && $permissions['deny'] === true) {
			if ($denyAll) {
				return true;
			}
			return false;
		}
		if ($permissions['deny'] === true) {
			return false;
		}
		if ($permissions['allow'] === false && $permissions['deny'] === false) {
			return false;
		}
		if ($permissions['allow'] === false) {
			return false;
		}

		return true;
	}
	
	/**
	 * Test current required rules against userModel
	 *
	 * @param object $userModel
	 * @param array $required
	 * @return boolean
	 */
	protected function _assert($userModel, $required) {
		
		$results = array();	
		foreach ($required as $role => $rule) {
			if (is_array($rule) && !is_numeric($role)) {					
				$arguments = array_merge(array($role), $rule);
			} elseif (is_string($rule) && !is_numeric($role)) {					
				$arguments = array($role, $rule);
			} else {
				$arguments = array($rule);
			}
			$results[] = call_user_func_array(array($userModel, 'is'), $arguments);
		}
		
		return in_array(true, $results, $strict=true);
	}
	
	/**
	 * Importer: Load Rules from Ini File
	 * @return mixed Imported Rules or False
	 */
	protected function _importFromIni() {
		
		$iniFilePath = APP . $this->loadFrom;
		
		if (!is_file($iniFilePath)) {
			trigger_error("Could not read INI file at '{$iniFilePath}'. Wrong path maybe?", E_USER_WARNING);
		} else {
			$iniRules = parse_ini_file($iniFilePath, true);
			$rules = array();
			foreach ($iniRules as $type => $controllerActions) {
				foreach ($controllerActions as $controllerAction => $perms) {
					
					list($controller, $action) = explode('.', $controllerAction);
					
					foreach ($perms as $iniPerm) {
						$ands = explode(',', $iniPerm);
						$subs = explode('.', $iniPerm);
						
						if (count($ands) > 1) {
							$permissions = $ands;
						} elseif (count($subs) == 2) {
							$permissions = array($subs[0] => $subs[1]);
						} else {
							$permissions = array($iniPerm);
						}
						
						$rules[$type]["{$controller}/{$action}"] = $permissions;
					}
				}
			}
			return $this->setRules($rules);
		}
		return false;
	}
	
	/**
	 * Importer: Load Rules from Model
	 * @return mixed Imported Rules or False
	 */
	protected function _importFromModel() {
		$ruleModel = ClassRegistry::init($this->loadFrom);
		$ruleModelResult = $ruleModel->find('all', array(
			'fields' => array('type', 'controller', 'action', 'role', 'subject', 'subjectId'),
			'conditions' => array('controller' => $controller->name, 'action' => $controller->action),
			'recursive' => -1
		));
		if (!empty($ruleModelResult)) {
			$rulesFound = $ruleModelResult[$ruleModel->alias];
			$rules = array();
			foreach ($rulesFound as $rule) {
				$subjects = array_merge(explode(',', $rule['subject']), array($rule['subjectId']));
				if (!empty($subjects)) {
					$rules[$rule['type']][] = array($rule['role'] => $subjects);
				} else {
					$rules[$rule['type']][] = array($rule['role']);
				}
			}
			return $this->setRules($rules);
		}
		return false;
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