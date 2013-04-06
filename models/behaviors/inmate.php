<?php 
/**
 * Jailson for CakePHP
 *   Access Control Plugin
 * 
 * @category CakePHP
 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
 * @package plugins.jailson
 * @subpackage plugins.jailson.behaviors
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @link http://github.com/m3nt0r/cakephp-jailson Repository/Docs
 * @copyright (c) 2010, Kjell Bublitz (http://cakealot.com)
 */
App::import('Lib', 'Jailson.Storage');

/**
 * Jailson - Inmate Behavior
 * 
 * @category CakePHP
 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
 * @package plugins.jailson
 * @subpackage plugins.jailson.behaviors
 */
class InmateBehavior extends ModelBehavior {
	
	/**
	 * Storage Model
	 * @var Inmate
	 */
	public $Inmate;
	
	/**
	 * Init Storage Model
	 */
	function setup($model, $config = array()) {
		// options
		$_defaultConfig = array(
			'cacheConfig' => 'default',
			'inmateModel' => 'Jailson.Inmate',
			'disableCache' => false
		);
		$this->settings[$model->alias] = array_merge($_defaultConfig, $config);	
		
		// load storage model
		if (!is_object($this->Inmate)) {
			$this->Inmate = ClassRegistry::init($this->settings[$model->alias]['inmateModel']);
		}
	}

	/**
	 * Before delete store the id of the object to Inmate
	 * 
	 * @see		InmateBehavior::afterDelete
	 * @param 	object 	$model
	 */
	function beforeDelete($model) {
		$this->_id = $model->id;
	}
	
	/**
	 * After delete remove all data for this object, using stored model id
	 * Clean up after.
	 *
	 * @see		InmateBehavior::beforeDelete
	 * @param 	object 	$model
	 */
	function afterDelete($model) {
		$model->id = $this->_id;
		$this->free($model);
		unset($this->_id);
	}
	
	
	/**
	 * Check if current object has given role
	 *
	 * @param 	object 	$model
	 * @param 	mixed 	$role string/array
	 * @param 	mixed	$sentence
	 * 
	 * @return 	boolean
	 */
	function has($model, $role, $sentence = null) {
		if (!is_array($role)) $role = array($role);
		
		$checked = $keys = array();
		$cached = $this->cachedRoles($model);
		
		if ($cached === false || $this->settings[$model->alias]['disableCache']) {
			foreach ($role as $part) {
				$keys[]= Storage::pack($model, $part, $sentence);
			}
			$found = $this->Inmate->retrieve($keys);
			if (count($found)) {			
				$this->_cache('write', $model, $found);
				$checked = array_fill(0, count($found), true); 
			}
		} else {
			foreach ($role as $part) {
				$key = Storage::pack($model, $part, $sentence);
				if (!in_array($key, $cached)) {
					$exists = $this->Inmate->retrieve($key);
					if (!empty($exists)) {
						$this->_cache('merge', $model, $exists, $cached);
						$checked[]= true;
					}
				} else {
					$checked[]= in_array($key, $cached);
				}
			}
		}
		return (array_sum($checked) == count($role));
	}
	
	/**
	 * Assigns given role to the current object
	 *
	 * @param 	object 	$model
	 * @param 	mixed 	$role string/array
	 * @param 	mixed 	$sentence
	 * 
	 * @return 	array
	 */
	function lockAs($model, $role, $sentence = null) {
		if (!is_array($role)) $role = array($role);
		
		$cached = (array) $this->cachedRoles($model);
		
		$keys = array();
		foreach ($role as $part) {
			$key = Storage::pack($model, $part, $sentence);
			if (!in_array($key, $cached)) // dont store cached
				$keys[]= $key;
		}
		
		if (!empty($keys))
			$stored = $this->Inmate->store($keys);
		
		if (!empty($stored)) {
			$this->_cache('merge', $model, $stored, $cached);
			return array_map(array('Storage', 'unpack'), $stored);
		}
		
		return false; // nothing to do
	}
	
	/**
	 * Removes given role from current object
	 * 
	 * @param 	object		$model
	 * @param 	mixed		$role
	 * @param 	mixed		$sentence
	 * 
	 * @return 	mixed		unpacked roles, or false if nothing to do
	 */
	function release($model, $role, $sentence = null) {
		$keys = $deleted = array();
		
		if (!is_array($role)) 
			$role = array($role);
		
		$cached = (array) $this->cachedRoles($model);
		
		if ($this->settings[$model->alias]['disableCache']) {
			foreach ($role as $part) {
				$keys[]= Storage::pack($model, $part, $sentence);
			}
		} else {
			foreach ($role as $part) {
				$key = Storage::pack($model, $part, $sentence);
				if (in_array($key, $cached)) // only try cached
					$keys[]= $key;
			}
		}
		
		if (!empty($keys))
			$deleted = $this->Inmate->remove($keys);
			
		if (!empty($deleted)) {
			$this->_cache('diff', $model, $deleted, $cached);
			return array_map(array('Storage', 'unpack'), $deleted);
		}
		
		return false; // nothing to do
	}
	
	/**
	 * Deletes all entries for object
	 *
	 * @param 	object 	$model
	 * @param 	mixed 	$role (optional)
	 * @param 	mixed 	$sentence (optional)
	 * 
	 * @return 	mixed 	roles data, or false if nothing to do
	 */
	function free($model, $role = null, $sentence = null) {
		
		$key = Storage::pack($model, $role, $sentence);
		$cached = (array) $this->cachedRoles($model);
		
		if (!in_array($key, $cached) && !$this->settings[$model->alias]['disableCache'])
			return false; // empty cache is 'free' enough
		
		$deleted = $this->Inmate->removeTree($key);
		
		if (empty($role)) { 
			$this->_cache('reset', $model);
		} 
		
		if (!empty($deleted)) {
			$this->_cache('diff', $model, $deleted, $cached);
			return array_map(array('Storage', 'unpack'), $deleted);
		}
		
		return false;
	}
	
	/**
	 * Returns a list of roles assigned to the current object
	 * 
	 * @param 	object 	$model
	 * @param 	mixed 	$justRoles 	If true, only current role names are returned
	 * 
	 * @return 	mixed 	roles data
	 */
	function roles($model, $justRoles = false) {
		
		$keys = (array) $this->Inmate->drilldown(Storage::inmateId($model));
		
		$result = array();
		foreach ($keys as $key) {
			$data = Storage::unpack($key);
			$result[ $data['role'] ][] = $data;
		}
		
		if ($justRoles)
			return array_keys($result);
		
		return $result;
	}
	
	/**
	 * Just for semantics: Alias to "is"
	 *
	 * @param 	object 	$model
	 * @param 	string 	$role   	member_of, created_by, seen_at, image_for, based_on
	 * @param 	mixed 	$sentence 	model object or any string
	 * @param 	boolean $create 	(optional) if true: make this semantic become real
	 * 
	 * @return 	array
	 */
	function did($model) {
		return call_user_method_array('is', $this, func_get_args());
	}
	
	/**
	 * Just for semantics: Alias to "is"
	 *
	 * @param 	object 	$model
	 * @param 	string 	$role   	member_of, created_by, seen_at, image_for, based_on
	 * @param 	mixed 	$sentence 	model object or any string
	 * @param 	boolean $create 	(optional) if true: make this semantic become real
	 * 
	 * @return 	array
	 */
	function was($model) {
		return call_user_method_array('is', $this, func_get_args());
	}
	
	/**
	 * Just for semantics: Alias to "is"
	 *
	 * @param 	object 	$model
	 * @param 	string 	$role   	member_of, created_by, seen_at, image_for, based_on
	 * @param 	mixed 	$sentence 	model object or any string
	 * @param 	boolean $create 	(optional) if true: make this semantic become real
	 * 
	 * @return 	array
	 */
	function can($model) {
		return call_user_method_array('is', $this, func_get_args());
	}
	
	/**
	 * Just for semantics: Alias to "is"
	 *
	 * @param 	object 	$model
	 * @param 	string 	$role   	member_of, created_by, seen_at, image_for, based_on
	 * @param 	mixed 	$sentence 	model object or any string
	 * @param 	boolean $create 	(optional) if true: make this semantic become real
	 * 
	 * @return 	array
	 */
	function isIn($model) {
		return call_user_method_array('is', $this, func_get_args());
	}
	
	/**
	 * Just for semantics
	 *
	 * @param 	object 	$model
	 * @param 	string 	$role   	member_of, created_by, seen_at, image_for, based_on
	 * @param 	mixed 	$sentence 	model object or any string
	 * @param 	boolean $create 	(optional) if true: make this semantic become real
	 * 
	 * @return 	array
	 */
	function is($model) {
		$args = func_get_args();		
		list($role, $sentence, $create) = $this->__parseArgs($args);
		if ($create) {
			return $this->lockAs($model, $role, $sentence);
		}
		return $this->has($model, $role, $sentence);
	}
	
	/**
	 * Just for semantics, reversed is()
	 *
	 * @param 	object 	$model
	 * @param 	string 	$role   	member_of, created_by, seen_at, image_for, based_on
	 * @param 	mixed 	$sentence 	model object or any string
	 * @param 	boolean $remove		(optional) if true, make this semantic become real
	 * 
	 * @return 	array
	 */
	function isNot($model) {
		$args = func_get_args();
		list($role, $sentence, $remove) = $this->__parseArgs($args);
		if ($remove) {
			return $this->release($model, $role, $sentence);
		}
		return !$this->has($model, $role, $sentence);
	}
	
	/**
	 * Wrapper for Cache::read
	 *  get currently cached roles for this object
	 *
	 * @param 	object 	$model
	 * @return 	mixed
	 */
	function cachedRoles($model) {
		if ($this->settings[$model->alias]['disableCache'])
			return array();
		
		$config = $this->settings[$model->alias]['cacheConfig'];
		$cache = Cache::read(Storage::cacheId($model), $config);
		return $cache;	
	}
	
	/**
	 * Wrapper for Cache::write
	 *  store data to the cache of the current object
	 * 
	 * @param 	string	$task 		Modify data task
	 * @param 	object	$model 		To build the cache key
	 * @param 	array	$data 		(optional) List of keys, or empty
	 * @param 	array	$cached 	(optional) Current cache
	 * 
	 * @return	array	Data that was written 
	 */
	protected function _cache($task, $model, $data = array(), $cached = null) {
		
		if (!$cached) 
			$cached = $this->cachedRoles($model);
			
		if ($task == 'merge') 
			$data = array_merge($cached, $data);
			
		if ($task == 'diff') 
			$data = array_diff($cached, $data);
		
		if ($task == 'reset')
			$data = array();
		
		if ($this->settings[$model->alias]['disableCache'])
			return $data;
			
		Cache::write(Storage::cacheId($model), Set::filter($data), $this->settings[$model->alias]['cacheConfig']);
		
		return $data;
	}
	
	/**
	 * Parse method arguments
	 * 
	 * @param	array	$args func_get_args
	 * @return	array	(sRole, mSentence, bSwitch)
	 */
	private function __parseArgs($args) {
		$model = $args[0];
		$role = Storage::role($args[1]);
		
		$sentence = null; 
		if (count($args) >= 3) {
			if (is_bool($args[2]) && count($args) == 3) {
				// allow bool as second param
				$switch = $args[2]; 
			} else {
				$sentence = $args[2];
			}
		}
		
		// optional third param (if there's a sentence)
		if (count($args) >= 4) {
			$switch = $args[3];		
		} else {
			if (!isset($switch)) 
				$switch = false;
		}
		
		return array($role, $sentence, $switch);
	}
}