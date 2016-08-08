<?php

require_once dirname(__FILE__).'/InstallUtils.class.php';

/**
 * uninstaller class.
 */
class Cosmoapi_Uninstaller
{
    /**
     * module install log.
     *
     * @var Legacy_ModuleInstallLog
     */
    public $mLog = null;

    /**
     * flag for force mode.
     *
     * @var bool
     */
    private $_mForceMode = false;

    /**
     * xoops module.
     *
     * @var XoopsModule
     */
    private $_mXoopsModule = null;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->mLog = new Legacy_ModuleInstallLog();
    }

    /**
     * set current xoops module.
     *
     * @param XoopsModule &$xoopsModule
     */
    public function setCurrentXoopsModule(&$xoopsModule)
    {
        $this->_mXoopsModule = &$xoopsModule;
    }

    /**
     * set force mode.
     *
     * @param bool $isForceMode
     */
    public function setForceMode($isForceMode)
    {
        $this->_mForceMode = $isForceMode;
    }

    /**
     * uninstall module.
     */
    private function _uninstallModule()
    {
        $dirname = $this->_mXoopsModule->get('dirname');
        $constpref = '_MI_'.strtoupper($dirname);
        $moduleHandler = &Cosmoapi_Utils::getXoopsHandler('module');
        if ($moduleHandler->delete($this->_mXoopsModule)) {
            $this->mLog->addReport(constant($constpref.'_INSTALL_MSG_MODULE_INFORMATION_DELETED'));
        } else {
            $this->mLog->addError(constant($constpref.'_INSTALL_ERROR_MODULE_INFORMATION_DELETED'));
        }
    }

    /**
     * uninstall tables.
     */
    private function _uninstallTables()
    {
        $dirname = $this->_mXoopsModule->get('dirname');
        $constpref = '_MI_'.strtoupper($dirname);
        $root = &XCube_Root::getSingleton();
        $db = &$root->mController->getDB();
        $tables = &$this->_mXoopsModule->getInfo('tables');
        if (is_array($tables)) {
            foreach ($tables as $table) {
                $tableName = str_replace(array('{prefix}', '{dirname}'), array(XOOPS_DB_PREFIX, $dirname), $table);
                $sql = sprintf('DROP TABLE `%s`;', $tableName);
                if ($db->query($sql)) {
                    $this->mLog->addReport(XCube_Utils::formatString(constant($constpref.'_INSTALL_MSG_TABLE_DOROPPED'), $tableName));
                } else {
                    $this->mLog->addError(XCube_Utils::formatString(constant($constpref.'_INSTALL_ERROR_TABLE_DOROPPED'), $tableName));
                }
            }
        }
    }

    /**
     * uninstall templates.
     */
    private function _uninstallTemplates()
    {
        Cosmoapi_InstallUtils::uninstallAllOfModuleTemplates($this->_mXoopsModule, $this->mLog, false);
    }

    /**
     * uninstall blocks.
     */
    private function _uninstallBlocks()
    {
        $dirname = $this->_mXoopsModule->get('dirname');
        $constpref = '_MI_'.strtoupper($dirname);
        Cosmoapi_InstallUtils::uninstallAllOfBlocks($this->_mXoopsModule, $this->mLog);
        $tplHandler = &Cosmoapi_Utils::getXoopsHandler('tplfile');
        $cri = new Criteria('tpl_module', $dirname);
        if (!$tplHandler->deleteAll($cri)) {
            $this->mLog->addError(XCube_Utils::formatString(constant($constpref.'_INSTALL_ERROR_BLOCK_TPL_DELETED'), $tplHandler->db->error()));
        }
    }

    /**
     * uninstall preferences.
     */
    private function _uninstallPreferences()
    {
        Cosmoapi_InstallUtils::uninstallAllOfConfigs($this->_mXoopsModule, $this->mLog);
    }

    /**
     * process report.
     */
    private function _processReport()
    {
        $dirname = $this->_mXoopsModule->get('dirname');
        $constpref = '_MI_'.strtoupper($dirname);
        if (!$this->mLog->hasError()) {
            $this->mLog->add(XCube_Utils::formatString(constant($constpref.'_INSTALL_MSG_MODULE_UNINSTALLED'), $this->_mXoopsModule->get('name')));
        } elseif (is_object($this->_mXoopsModule)) {
            $this->mLog->addError(XCube_Utils::formatString(constant($constpref.'_INSTALL_ERROR_MODULE_UNINSTALLED'), $this->_mXoopsModule->get('name')));
        } else {
            $this->mLog->addError(XCube_Utils::formatString(constant($constpref.'_INSTALL_ERROR_MODULE_UNINSTALLED'), 'something'));
        }
    }

    /**
     * execute uninstall.
     *
     * @return bool
     */
    public function executeUninstall()
    {
        $this->_uninstallTables();
        if (!$this->_mForceMode && $this->mLog->hasError()) {
            $this->_processReport();

            return false;
        }
        if ($this->_mXoopsModule->get('mid') != null) {
            $this->_uninstallModule();
            if (!$this->_mForceMode && $this->mLog->hasError()) {
                $this->_processReport();

                return false;
            }
            $this->_uninstallTemplates();
            if (!$this->_mForceMode && $this->mLog->hasError()) {
                $this->_processReport();

                return false;
            }
            $this->_uninstallBlocks();
            if (!$this->_mForceMode && $this->mLog->hasError()) {
                $this->_processReport();

                return false;
            }
            $this->_uninstallPreferences();
            if (!$this->_mForceMode && $this->mLog->hasError()) {
                $this->_processReport();

                return false;
            }
        }
        $this->_processReport();

        return true;
    }
}
