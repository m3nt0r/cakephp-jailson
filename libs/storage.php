<?php
class Storage extends Object {
	
	/**
	 * Suffixes for semantics
	 */
	const ROLE_SUFFIXES = '/(_(of|at|on|by|for|in))?\Z/';
	
	/**
	 * Key Separator
	 */
	const SEP = '/';
	
	/**
	 * Key Structure
	 */
	static $packStruct = array(
		'who', 'whoId', 'role', 'what', 'whatId'
	);
	
	/**
	 * Create string based on model, role and (optional) subject
	 *
	 * @param 	object 	$model
	 * @param 	string 	$role
	 * @param 	mixed 	$sentence (optional)
	 * 
	 * @return	string	a/nice/role/key
	 */
	public static function pack($model, $role, $sentence = null) {
		return 
			self::inmateId($model) . self::SEP . self::role($role) . 
			($sentence ? self::SEP . self::sentenceId($sentence) : '');
	}
	
	/**
	 * Extract all data from key and return assoc array
	 *
	 * @param 	string 	$key
	 * @return 	array
	 */
	public static function unpack($key) {
		$data = array();
		$values = explode(self::SEP, $key);
		foreach ($values as $index => $value) {
			$data[self::$packStruct[$index]] = $value;
		}
		return $data;
	}
	
	/**
	 * Build role id
	 * 
	 * @param 	object 	$model
	 * @return 	string
	 */
	public static function role($role) {
		$role = preg_replace(self::ROLE_SUFFIXES, '', $role);
		return $role;
	}
	
	/**
	 * Build inmate id
	 * 
	 * @param 	object 	$model
	 * @return 	string
	 */
	public static function inmateId($model) {
		return $model->alias . ($model->id ? self::SEP . $model->id : '');
	}
	
	/**
	 * Build sentence id
	 * 
	 * @param 	object 	$model
	 * @return 	string
	 */
	public static function sentenceId($var) {
		if (is_object($var)) {
			return self::inmateId($var); 
		}
		return $var;
	}
	
	/**
	 * Cache key for object
	 * 
	 * @param 	object 	$model
	 * @return 	string
	 */
	public static function cacheId($model) {
		return self::inmateId($model);
	}
}