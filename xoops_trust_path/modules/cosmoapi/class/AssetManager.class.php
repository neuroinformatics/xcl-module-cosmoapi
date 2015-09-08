<?php

/**
 * asset manager
 */
class Cosmoapi_AssetManager {

	/**
	 * dirname
	 * @var string
	 */
	public $mDirname = '';

	/**
	 * trust dirname
	 * @var string
	 */
	public $mTrustDirname = '';

	/**
	 * asset list
	 * @var string[][][]
	 */
	public $mAssetList = array();

	/**
	 * object cache
	 * @var object[][]
	 */
	private $_mCache = array();

	/**
	 * constructor
	 * 
	 * @param string $dirname
	 */
	public function __construct($dirname, $trustDirname) {
		$this->mDirname = $dirname;
		$this->mTrustDirname = $trustDirname;
	}

	/**
	 * get instance
	 * 
	 * @param string $dirname
	 * @param string $trustDirname
	 * @return {Trustdirname}_AssetManager
	 */
	public static function &getInstance($dirname, $trustDirname) {
		static $instance = array();
		if (!isset($instance[$dirname]))
			$instance[$dirname] = new self($dirname, $trustDirname);
		return $instance[$dirname];
	}

	/**
	 * get object
	 * 
	 * @param string $type
	 * @param string $name
	 * @param bool $isAdmin
	 * @param string $mode
	 * @return &object<XCube_ActionFilter, XCube_ActionForm, XoopsObjectGenericHandler>
	 */
	public function &getObject($type, $name, $isAdmin = false, $mode = null) {
		if (isset($this->_mCache[$type][$name]))
			return $this->_mCache[$type][$name];
		$instance = null;
		$methodName = 'create' . ucfirst($name) . ucfirst($mode) . ucfirst($type);
		if (method_exists($this,$methodName))
			$instance =& $this->$methodName();
		if ($instance === null)
			$instance =& $this->_fallbackCreate($type, $name, $isAdmin, $mode);
		$this->_mCache[$type][$name] =& $instance;
		return $instance;
	}

	/**
	 * get role name
	 * 
	 * @param string $role
	 * @return string
	 */
	public function getRoleName($role) {
		return 'Module.' . $this->mDirname . '.' . $role;
	}

	/**
	 * fallback create object
	 * 
	 * @param string $type
	 * @param string $name
	 * @param bool $isAdmin
	 * @param string $mode
	 * @return &object<XCube_ActionFilter, XCube_ActionForm, XoopsObjectGenericHandler>
	 */
	private function &_fallbackCreate($type, $name, $isAdmin = false, $mode = null) {
		$className = null;
		$instance = null;
		if (isset($this->mAssetList[$type][$name]['class'])) {
			$asset = $this->mAssetList[$type][$name];
			if (isset($asset['absPath']) && $this->_loadClassFile($asset['absPath'], $asset['class'])) {
				$className = $asset['class'];
			}
			if ($className == null && isset($asset['path'])) {
				if ($this->_loadClassFile($this->_getPublicPath() . $asset['path'], $asset['class']))
					$className = $asset['class'];
				if ($className == null && $this->_loadClassFile($this->_getTrustPath() . $asset['path'], $asset['class']))
					$className = $asset['class'];
			}
		} 
		if ($className == null) {
			switch ($type) {
			case 'filter':
				$className = $this->_getFilterName($name, $isAdmin);
				break;
			case 'form':
				$className = $this->_getActionFormName($name, $isAdmin, $mode);
				break;
			case 'handler':
				$className = $this->_getHandlerName($name);
				break;
			default:
				return $instance;
			}
		}
		if ($type == 'handler') {
			$root =& XCube_Root::getSingleton();
			$instance = new $className($root->mController->getDB(), $this->mDirname);
		} else {
			$instance = new $className();
		}
		return $instance;
	}

	/**
	 * get filter name
	 * 
	 * @param string $name
	 * @param bool $isAdmin
	 * @return string
	 */
	private function _getFilterName($name, $isAdmin = false) {
		$name = ucfirst($name) . 'FilterForm';
		$path = 'forms/' . $name . '.class.php';
		$className = ucfirst($this->mTrustDirname) . ($isAdmin ? '_Admin_' : '_') . $name;
		return ($this->_loadClassFile($this->_getPublicPath($isAdmin) . $path, $className) || $this->_loadClassFile($this->_getTrustPath($isAdmin) . $path, $className)) ? $className : null;
	}

	/**
	 * get action form name
	 * 
	 * @param string $name
	 * @param bool $isAdmin
	 * @param string $mode
	 * @return string
	 */
	private function _getActionFormName($name, $isAdmin = false, $mode = null) {
		$name = ucfirst($name) . ucfirst($mode) . 'Form';
		$path = 'forms/' . $name . '.class.php';
		$className = ucfirst($this->mTrustDirname) . ($isAdmin ? '_Admin_' : '_') . $name;
		return ($this->_loadClassFile($this->_getPublicPath($isAdmin) . $path,$className) || $this->_loadClassFile($this->_getTrustPath($isAdmin) . $path, $className)) ? $className : null;
	}

	/**
	 * get handler name
	 * 
	 * @param   string  $name
	 * @return  string
	 */
	private function _getHandlerName($name) {
		$path = 'class/handler/' . ucfirst($name) . '.class.php';
		$className = ucfirst($this->mTrustDirname) . '_' . ucfirst($name) . 'Handler';
		return ($this->_loadClassFile($this->_getPublicPath() . $path, $className) || $this->_loadClassFile($this->_getTrustPath() . $path,$className)) ? $className : null;
	}

	/**
	 * load class file
	 * 
	 * @param   string  $path
	 * @param   string  $class
	 * @return  bool
	 */
	private function _loadClassFile($path, $class) {
		if (!file_exists($path))
			return false;
		$mydirname = $this->mDirname;
		$mytrustdirname = $this->mTrustDirname;
		require_once $path;
		return class_exists($class);
	}

	/**
	 * get public path
	 * 
	 * @param bool $isAdmin
	 * @return string
	 */
	private function _getPublicPath($isAdmin = false) {
		return XOOPS_MODULE_PATH . '/' . $this->mDirname . ($isAdmin ? '/admin/' : '/');
	}

	/**
	 * _getTrustPath
	 * 
	 * @param bool $isAdmin
	 * @return string
	 */
	private function _getTrustPath($isAdmin = false) {
		return XOOPS_TRUST_PATH . '/modules/' . $this->mTrustDirname . ($isAdmin ? '/admin/' : '/');
	}
}

