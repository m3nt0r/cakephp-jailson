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
App::import('Core', 'Set');
/**
 * Jailson - Inmate Model
 * 
 * @category CakePHP
 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
 * @package plugins.jailson
 * @subpackage plugins.jailson.models
 */
class Inmate extends JailsonAppModel {
	
	/**
	 * Store any non duplicate key to the database.
	 * 
	 * @param	array	$keys
	 * @return	array	List of saved keys
	 */
	public function store($keys) {
		if (empty($keys)) return array();
		if (!is_array($keys)) $keys = array($keys);
		
		$existing = $this->retrieve($keys);
		$newKeys = array_diff($keys, $existing);
		
		$insert = array();
		foreach ($newKeys as $key) {
			$insert[]['key'] = $key;
		}
		
		if (count($newKeys))
			$this->saveAll($this->create($insert));
				
		return $newKeys;
	}
	
	/**
	 * Search Database for a specific key or a list of specific keys
	 * 
	 * @param	array	$keys
	 * @return	array	List of found keys
	 */
	public function retrieve($keys) {
		if (empty($keys)) return array();
		$result = (array) $this->find('all', array(
			'fields' => array("{$this->alias}.key"),
			'conditions' => array("{$this->alias}.key" => $keys),	
			'recursive' => -1
		));
		return Set::extract("/{$this->alias}/key", $result);
	}
	
	/**
	 * Search Database for a partial key using LIKE, returning all results
	 * 
	 * @param 	string 	$partialKey The beginning of a key
	 * @return	array	List of found keys
	 */
	public function drilldown($partialKey) {
		if (empty($partialKey)) return array();
		$result = (array) $this->find('all', array(
			'conditions' => array("{$this->alias}.key LIKE" => $partialKey . '%'),	
			'recursive' => -1
		));
		return Set::extract("/{$this->alias}/key", $result);
	}
	
	/**
	 * Remove matching key(s) from storage
	 * 
	 * @param 	mixed 	$keys 	array or string
	 * @return 	array 	List of deleted keys
	 */
	public function remove($keys) {
		if (empty($keys)) return array();
		if (!is_array($keys)) $keys = array($keys);
		
		$result = $this->find('all', array(
			'conditions' => array("{$this->alias}.key" => $keys),	
			'recursive' => -1
		));
		if (!$result) return array();
		
		$records = array(
			'id' => Set::extract("/{$this->alias}/id", $result)
		);
		$this->deleteAll($records, false);
		return Set::extract("/{$this->alias}/key", $result);
	}
	
	/**
	 * Remove matching partial key(s) from storage
	 * 
	 * # example db entries: 
	 * User/12/foo
	 * User/12/foo/Bar
	 * User/12/foo/Bar/Baz
	 * 
	 * partialKey 'User/12/foo' removes also /Bar and /Bar/Baz
	 * 
	 * @param 	string 	$partialKey The beginning of a key
	 * @return 	array 	List of deleted keys
	 */
	public function removeTree($partialKey) {
		if (empty($partialKey)) return array();
		$result = (array) $this->find('all', array(
			'conditions' => array("{$this->alias}.key LIKE" => $partialKey . '%'),	
			'recursive' => -1
		));
		if (!$result) return array();
		
		$records = array(
			'id' => Set::extract("/{$this->alias}/id", $result)
		);
		$this->deleteAll($records, false);
		return Set::extract("/{$this->alias}/key", $result);
	}
}
?>