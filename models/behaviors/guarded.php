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
App::import('Behavior', 'Jailson.Inmate');
/**
 * Jailson - Guarded Behavior
 * 
 * @category CakePHP
 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
 * @package plugins.jailson
 * @subpackage plugins.jailson.behaviors
 */
class GuardedBehavior extends InmateBehavior {
	
	/**
	 * The scope of this guard
	 * @var Model
	 */
	public $GuardedObject;
	
	/**
	 * Init Storage Model
	 */
	function setup($model, $config = array()) {
		// options
		$_defaultConfig = array(
			'inmateModel' => 'Jailson.Inmate',
			'inmates' => array('User'),
			
			'find' => array(),
			'save' => array(),
			'delete' => array(),
		);
		$this->settings[$model->alias] = array_merge($_defaultConfig, $config);	
		
		// load storage model
		if (!is_object($this->Inmate)) {
			$this->Inmate = ClassRegistry::init($this->settings[$model->alias]['inmateModel']);
		}
	}
	
	protected function _allowedIds($model, $permissions) {
		$storedPerms = array();
		foreach ($permissions as $role) {
			$key = $this->_pack($this->GuardedObject, $role, $model);
			$storedPerms = array_map(array($this,'_unpack'), $this->Inmate->drilldown($key));
		}
		return Set::extract('/subject_id', $storedPerms);
	}
	
	function beforeFind(&$model, $query) {
		$permissions = $this->_findPerms($model);
		
		if (!empty($permissions)) {
			$allowedIds = $this->_allowedIds($model, $permissions);
			if (!empty($allowedIds)) {
				$id = "{$model->alias}.id";
				if (empty($query['conditions'][$id])) $query['conditions'][$id] = array();
				$query['conditions'][$id] = array_merge($query['conditions'][$id], $allowedIds);
			} else {
				// nasty hack
				//---------------------------------
				// either this, or return false - but that will make find() return null.
				// null sucks in many ways.
				//---------------------------------
				$query['conditions'] = array($id.' < 1');
			}
		}
		return $query;
	}
	
	function beforeSave(&$model) { }
	function beforeDelete(&$model, $cascade = true) { }
	
	
	
	protected function _findPerms($model) {
		return $this->settings[$model->alias]['find'];
	}
	protected function _savePerms($model) {
		return $this->settings[$model->alias]['save'];
	}
	protected function _deletePerms($model) {
		return $this->settings[$model->alias]['delete'];
	}
	
}