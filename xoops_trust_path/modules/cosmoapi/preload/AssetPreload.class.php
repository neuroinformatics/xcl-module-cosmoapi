<?php

require_once dirname(dirname(__FILE__)) . '/class/Utils.class.php';

/**
 * asset preload base class
 */
class Cosmoapi_AssetPreloadBase extends XCube_ActionFilter {

	/**
	 * dirname
	 * @var string
	 */
	public $mDirname = null;

	/**
	 * prepare
	 * 
	 * @param string $dirname
	 * @param string $trustDirname
	 */
	public static function prepare($dirname, $trustDirname) {
		$root =& XCube_Root::getSingleton();
		$instance = new self($root->mController);
		$instance->mDirname = $dirname;
		if (!defined('COSMOAPI_TRUST_DIRNAME'))
			define('COSMOAPI_TRUST_DIRNAME', $trustDirname);
		$root->mController->addActionFilter($instance);
	}

	/**
	 * pre block filter
	 */
	public function preBlockFilter() {
		static $isFirst = true;
		$prefix = ucfirst(COSMOAPI_TRUST_DIRNAME);
		if ($isFirst) {
			// global delegates - activate only first module
			$this->mRoot->mDelegateManager->add('Module.' . COSMOAPI_TRUST_DIRNAME . '.Global.Event.GetAssetManager', $prefix . '_AssetPreloadBase::getManager');
			$this->mRoot->mDelegateManager->add('Legacy_Utils.CreateModule', $prefix . '_AssetPreloadBase::getModule');
			$this->mRoot->mDelegateManager->add('Legacy_Utils.CreateBlockProcedure', $prefix . '_AssetPreloadBase::getBlock');
			$isFirst = false;
		}
	}

	/**
	 * get manager
	 * 
	 * @param {Trustdirname}_AssetManager &$obj
	 * @param string $dirname
	 */
	public static function getManager(&$obj, $dirname) {
		require_once XOOPS_TRUST_PATH . '/modules/'. COSMOAPI_TRUST_DIRNAME . '/class/AssetManager.class.php';
		$className = ucfirst(COSMOAPI_TRUST_DIRNAME) . '_AssetManager';
		$obj = call_user_func_array($className . '::getInstance', array($dirname, COSMOAPI_TRUST_DIRNAME));
	}

	/**
	 * get module
	 * 
	 * @param Legacy_AbstractModule &$obj
	 * @param XoopsModule $module
	 */
	public static function getModule(&$obj, $module) {
		if ($module->getInfo('trust_dirname') == COSMOAPI_TRUST_DIRNAME) {
			$mytrustdirname = COSMOAPI_TRUST_DIRNAME;
			require_once XOOPS_TRUST_PATH . '/modules/'. COSMOAPI_TRUST_DIRNAME . '/class/Module.class.php';
			$className = ucfirst(COSMOAPI_TRUST_DIRNAME) . '_Module';
			$obj = new $className($module);
		}
	}

	/**
	 * get block
	 * 
	 * @param Legacy_AbstractBlockProcedure &$obj
	 * @param XoopsBlock $block
	 */
	public static function getBlock(&$obj, $block) {
		$moduleHandler =& xoops_gethandler('module');
		$module =& $moduleHandler->get($block->get('mid'));
		if (is_object($module) && $module->getInfo('trust_dirname') == COSMOAPI_TRUST_DIRNAME) {
			require_once XOOPS_TRUST_PATH . '/modules/'. COSMOAPI_TRUST_DIRNAME . '/blocks/' . $block->get('func_file');
			$className = ucfirst(COSMOAPI_TRUST_DIRNAME) . '_' . substr($block->get('show_func'), 4);
			$obj = new $className($block);
		}
	}

}
