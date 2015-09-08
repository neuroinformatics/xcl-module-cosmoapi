<?php

require_once dirname(__FILE__) . '/InstallUtils.class.php';

/**
 * updater class
 */
class Cosmoapi_Updater {

	/**
	 * module install log
	 * @var Legacy_ModuleInstallLog
	 */
	public $mLog = null;

	/**
	 * milestone
	 * @var string[]
	 */
	private $_mMileStone = array();

	/**
	 * current xoops module
	 * @var XoopsModule
	 */
	private $_mCurrentXoopsModule = null;

	/**
	 * target xoops module
	 * @var XoopsModule
	 */
	private $_mTargetXoopsModule = null;

	/**
	 * current module version
	 * @var int
	 */
	private $_mCurrentVersion = 0;

	/**
	 * target module version
	 * @var int
	 */
	private $_mTargetVersion = 0;

	/**
	 * flag for force mode
	 * @var bool
	 */
	private $_mForceMode = false;

	/**
	 * constructor
	 */
	public function __construct() {
		$this->mLog = new Legacy_ModuleInstallLog();
	}

	/**
	 * set force mode
	 *
	 * @param bool $isForceMode
	 */
	public function setForceMode($isForceMode) {
		$this->_mForceMode = $isForceMode;
	}

	/**
	 * set current xoops module
	 *
	 * @param XoopsModule &$module
	 */
	public function setCurrentXoopsModule(&$module) {
		$dirname = $module->get('dirname');
		$moduleHandler =& Cosmoapi_Utils::getXoopsHandler('module');
		$cloneModule =& $moduleHandler->create();
		$cloneModule->unsetNew();
		$cloneModule->set('mid', $module->get('mid'));
		$cloneModule->set('name', $module->get('name'));
		$cloneModule->set('version', $module->get('version'));
		$cloneModule->set('last_update', $module->get('last_update'));
		$cloneModule->set('weight', $module->get('weight'));
		$cloneModule->set('isactive', $module->get('isactive'));
		$cloneModule->set('dirname', $dirname);
		// $cloneModule->set('trust_dirname', $module->get('trust_dirname'));
		$cloneModule->set('hasmain', $module->get('hasmain'));
		$cloneModule->set('hasadmin', $module->get('hasadmin'));
		$cloneModule->set('hasconfig', $module->get('hasconfig'));
		$this->_mCurrentXoopsModule =& $cloneModule;
		$this->_mCurrentVersion = $cloneModule->get('version');
	}

	/**
	 * set target xoops module
	 *
	 * @param XoopsModule &$module
	 */
	public function setTargetXoopsModule(&$module) {
		$this->_mTargetXoopsModule =& $module;
		$this->_mTargetVersion = $this->getTargetPhase();
	}

	/**
	 * get current version
	 *
	 * @return int
	 */
	public function getCurrentVersion() {
		return intval($this->_mCurrentVersion);
	}

	/**
	 * get target phase
	 *
	 * @return int
	 */
	public function getTargetPhase() {
		ksort($this->_mMileStone);
		foreach ($this->_mMileStone as $tVer => $tMethod) {
			if ($tVer >= $this->getCurrentVersion())
				return intval($tVer);
		}
		return $this->_mTargetXoopsModule->get('version');
	}

	/**
	 * check whether updater has phase update method
	 *
	 * @return bool
	 */
	public function hasUpgradeMethod() {
		ksort($this->_mMileStone);
		foreach ($this->_mMileStone as $tVer => $tMethod) {
			if ($tVer >= $this->getCurrentVersion() && is_callable(array($this, $tMethod)))
				return true;
		}
		return false;
	}

	/**
	 * check whether it is latest update now
	 *
	 * @return bool
	 */
	public function isLatestUpgrade() {
		return ($this->_mTargetXoopsModule->get('version') == $this->getTargetPhase());
	}

	/**
	 * update module templates
	 */
	private function _updateModuleTemplates() {
		Cosmoapi_InstallUtils::uninstallAllOfModuleTemplates($this->_mTargetXoopsModule, $this->mLog);
		Cosmoapi_InstallUtils::installAllOfModuleTemplates($this->_mTargetXoopsModule, $this->mLog);
	}

	/**
	 * update blocks
	 */
	private function _updateBlocks() {
		Cosmoapi_InstallUtils::smartUpdateAllOfBlocks($this->_mTargetXoopsModule, $this->mLog);
	}

	/**
	 * update preferences
	 */
	private function _updatePreferences() {
		Cosmoapi_InstallUtils::smartUpdateAllOfConfigs($this->_mTargetXoopsModule, $this->mLog);
	}

	/**
	 * execute upgrade
	 *
	 * @return bool
	 */
	public function executeUpgrade() {
		return ($this->hasUpgradeMethod() ? $this->_callUpgradeMethod() : $this->executeAutomaticUpgrade());
	}

	/**
	 * call upgrade method
	 *
	 * @return bool
	 */
	private function _callUpgradeMethod() {
		ksort($this->_mMileStone);
		foreach ($this->_mMileStone as $tVer => $tMethod) {
			if ($tVer >= $this->getCurrentVersion() && is_callable(array($this, $tMethod)))
				return $this->$tMethod();
		}
		return false;
	}

	/**
	 * execute automatic upgrade
	 *
	 * @return bool
	 */
	public function executeAutomaticUpgrade() {
		$dirname = $this->_mCurrentXoopsModule->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$this->mLog->addReport(constant($constpref . '_INSTALL_MSG_UPDATE_STARTED'));
		$this->_updateModuleTemplates();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->_updateBlocks();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->_updatePreferences();
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->saveXoopsModule($this->_mTargetXoopsModule);
		if (!$this->_mForceMode && $this->mLog->hasError()) {
			$this->_processReport();
			return false;
		}
		$this->_processReport();
		return true;
	}

	/**
	 * save xoops module
	 *
	 * @param XoopsModule &$module
	 */
	public function saveXoopsModule(&$module) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$moduleHandler =& Cosmoapi_Utils::getXoopsHandler('module');
		if ($moduleHandler->insert($module)) {
			$this->mLog->addReport(constant($constpref . '_INSTALL_MSG_UPDATE_FINISHED'));
		} else {
			$this->mLog->addError(constant($constpref . '_INSTALL_ERROR_UPDATE_FINISHED'));
		}
	}

	/**
	 * process report
	 */
	private function _processReport() {
		$dirname = $this->_mCurrentXoopsModule->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		if (!$this->mLog->hasError()) {
			$this->mLog->add(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_MODULE_UPDATED'), $this->_mCurrentXoopsModule->get('name')));
		} else {
			$this->mLog->add(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_MODULE_UPDATED'), $this->_mCurrentXoopsModule->get('name')));
		}
	}

}

