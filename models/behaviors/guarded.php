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
 * Jailson - Guarded Behavior
 * 
 * @category CakePHP
 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
 * @package plugins.jailson
 * @subpackage plugins.jailson.behaviors
 */
class GuardedBehavior extends ModelBehavior {
	
	/**
	 * Jailson storage model
	 * @var Model
	 */
	public $Inmate;
	
	/**
	 * The scope of this guard
	 * @var Model
	 */
	public $GuardedObject;
	
	/**
	 * Init Storage Model
	 */
	public function setup(&$model, $config = array()) {
		// options
		$_defaultConfig = array(
			'inmateModel' => 'Jailson.Inmate',
			
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
	
	/**
	 * Enable permission chain
	 */
	public function beforeFind(&$model, $query) {
		$this->_bind($model, $this->_perms($model, 'find'));
	}
	
	/**
	 * Remove permission chain
	 */
	public function afterFind(&$model) {
		$this->_unbind($model);
	}
	
	public function beforeSave(&$model) { }
	public function beforeDelete(&$model, $cascade = true) { }
	

	/**
	 * Set chain
	 */
	protected function _bind(&$model, $roles = array()) {
		
		$inmateModel = $this->settings[$model->alias]['inmateModel'];
		
		$conditions = array(
			'Jailson.what' => $model->alias
		);
		
		if (!empty($roles)) {
			$conditions = array_merge($conditions, 
				array('Jailson.role' => $roles)
			);
		} else {
			return false; // dont bind if there are no rules.
		}
		
		$model->bindModel(array('hasOne' => array(
			'Jailson' => array(
				'className' => $inmateModel,
				'type' => 'INNER',
				'foreignKey' => 'whatId',
				'conditions' => $conditions
			)
		)));
	}
	
	/**
	 * Remove chain
	 */
	protected function _unbind(&$model) {
		$model->unbindModel(array('hasOne' => array(
			'Jailson'
		)), $reset = false);
	}
	
	/**
	 * Read from settings array (shortcut)
	 */
	protected function _perms($model, $action) {
		return $this->settings[$model->alias][$action];
	}
	
	/**
	 * Public method to retrieve all IDs that match
	 * for given role and the current model.
	 *
	 * (unused)
	 */
	public function getAllowed($model, $roles) {
		$who = $this->GuardedObject;
		$allowed = $this->Inmate->find('all', array(
			'fields' => array('whatId'),
			'conditions' => array(
				'who' => $who->alias,
				'whoId' => $who->id,
				'what' => $model->alias,
				'whatId NOT' => null,
				'role' => $roles
			)
		));
		return Set::extract('/Inmate/whatId', $allowed);
	}
}