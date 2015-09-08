<?php

/**
 * utilities
 */
class Cosmoapi_Utils {

	/**
	 * get xoops handler
	 * 
	 * @param string $name
	 * @param bool $optional
	 * @return XoopsObjectHandler&
	 */
	public static function &getXoopsHandler($name, $optional = false) {
		return xoops_gethandler($name, $optional);
	}

	/**
	 * get module handler
	 * 
	 * @param string $name
	 * @param string $dirname
	 * @return XoopsObjectHandleer&
	 */
	public static function &getModuleHandler($name, $dirname) {
		return xoops_getmodulehandler($name, $dirname);
	}

	/**
	 * get trust module handler
	 * 
	 * @param string $name
	 * @param string $trustDirname
	 * @return XoopsObjectHandleer&
	 */
	public static function &getTrustModuleHandler($name, $trustDirname) {
		$path = XOOPS_TRUST_PATH. '/modules/'. $trustDirname .'/class/handler/' . ucfirst($name) . '.class.php';
		$className = ucfirst($trustDirname) . '_' . ucfirst($name) . 'Handler';
		if (!file_exists($path))
                       	return false;
               	require_once $path;
               	if (!class_exists($className))
			return false;
		$root =& XCube_Root::getSingleton();
		$instance = new $className($root->mController->getDB());
		return $instance;
	}

	/**
	 * get environment variable
	 * 
	 * @param string $key
	 * @return string
	 */
	public static function getEnv($key) {
		return @getenv($key);
	}

	/**
	 * get module configs
	 * 
	 * @param string $dirname
	 * @param string $key
	 * @return mixed
	 */
	public static function getModuleConfig($dirname, $key) {
		$handler =& self::getXoopsHandler('config');
		$configArr = $handler->getConfigsByDirname($dirname);
		return $configArr[$key];
	}

	/**
	 * check whether user is administrator
	 *
	 * @param string $dirname
	 * @return bool
	 */
	public static function isAdmin($dirname = false) {
		$root =& XCube_Root::getSingleton();
		if ($root->mContext->mUser->isInRole('Site.Owner'))
			return true;
		if (empty($dirname))
			return false;
		$root->mRoleManager->loadRolesByDirname($dirname);
		return $root->mContext->mUser->isInRole('Module.' . $dirname . '.Admin');
	}

}

