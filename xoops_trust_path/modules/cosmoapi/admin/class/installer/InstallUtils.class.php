<?php

/**
 * install utilities class
 */
class Cosmoapi_InstallUtils {

	/**
	 * install sql automatically
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function installSQLAutomatically(&$module, &$log) {
		$dirname = $module->get('dirname');
		$trustDirname = $module->getInfo('trust_dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$sqlFileInfo =& $module->getInfo('sqlfile');
		if (!isset($sqlFileInfo[XOOPS_DB_TYPE]))
			return true;
		$sqlFile = $sqlFileInfo[XOOPS_DB_TYPE];
		$sqlFilePath = sprintf('%s/%s/%s', XOOPS_MODULE_PATH, $dirname, $sqlFile);
		if (!file_exists($sqlFilePath))
			$sqlFilePath = sprintf('%s/modules/%s/%s', XOOPS_TRUST_PATH, $trustDirname, $sqlFile);
		require_once XOOPS_MODULE_PATH . '/legacy/admin/class/Legacy_SQLScanner.class.php';
		$scanner = new Legacy_SQLScanner();
		$scanner->setDB_PREFIX(XOOPS_DB_PREFIX);
		$scanner->setDirname($dirname);
		if (!$scanner->loadFile($sqlFilePath)) {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_SQL_FILE_NOT_FOUND'), $sqlFile));
			return false;
		}
		$scanner->parse();
		$root =& XCube_Root::getSingleton();
		$db =& $root->mController->getDB();
		foreach ($scanner->getSQL() as $sql) {
			if (!$db->query($sql)) {
				$log->addError($db->error());
				return false;
			}
		}
		$log->addReport(constant($constpref . '_INSTALL_MSG_DB_SETUP_FINISHED'));
		return true;
	}

	/**
	 * DB query
	 *
	 * @param string $query
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function DBquery($query, &$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		require_once XOOPS_MODULE_PATH . '/legacy/admin/class/Legacy_SQLScanner.class.php';
		$scanner = new Legacy_SQLScanner();
		$scanner->setDB_PREFIX(XOOPS_DB_PREFIX);
		$scanner->setDirname($dirname);
		$scanner->setBuffer($query);
		$scanner->parse();
		$sqls = $scanner->getSQL();
		$root =& XCube_Root::getSingleton();
		$successFlag = true;
		foreach ($sqls as $sql) {
			if ($root->mController->mDB->query($sql)) {
				$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_SQL_SUCCESS'), $sql));
			} else {
				$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_SQL_ERROR'), $sql));
				$successFlag = false;
			}
		}
		return $successFlag;
	}

	/**
	 * replace dirname
	 *
	 * @param string $from
	 * @param string $dirname
	 * @param string $trustDirname
	 * @return {string 'public', string 'trust'}
	 */
	public static function replaceDirname($from, $dirname, $trustDirname = null) {
		if (strpos($from, '{dirname}') === false) {
			return array(
				'public' => $dirname . '_' . $from,
				'trust' => ($trustDirname != null) ? $from : null
			);
		}
		return array(
			'public' => str_replace('{dirname}', $dirname, $from),
			'trust' => ($trustDirname != null) ? str_replace('{dirname}', $trustDirname, $from) : null
		);
	}

	/**
	 * read template file
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 * @param string $filename
	 * @param bool $isBlock
	 * @return string
	 */
	public static function readTemplateFile($dirname, $trustDirname, $filename, $isBlock = false) {
		$filePath = sprintf('%s/%s/templates/%s%s', XOOPS_MODULE_PATH, $dirname, ($isBlock ? 'blocks/' : ''), $filename);
		if (!file_exists($filePath)) {
			$filePath = sprintf('%s/modules/%s/templates/%s%s', XOOPS_TRUST_PATH, $trustDirname, ($isBlock ? 'blocks/' : ''), $filename);
			if (!file_exists($filePath))
				return false;
		}
		if (!($lines = file($filePath)))
			return false;
		$tplData = '';
		foreach ($lines as $line)
			$tplData .= str_replace("\n", "\r\n", str_replace("\r\n", "\n", $line));
		return $tplData;
	}

	/**
	 * install all of module templates
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 */
	public static function installAllOfModuleTemplates(&$module, &$log) {
		$templates =& $module->getInfo('templates');
		if (is_array($templates) && count($templates) > 0) {
			foreach ($templates as $template)
				self::installModuleTemplate($module, $template, $log);
		}
	}

	/**
	 * install module template
	 *
	 * @param XoopsModule &$module
	 * @param string[] $template
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function installModuleTemplate(&$module, $template, &$log) {
		$dirname = $module->get('dirname');
		$trustDirname = $module->getInfo('trust_dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$tplHandler =& Cosmoapi_Utils::getXoopsHandler('tplfile');
		$filename = self::replaceDirname(trim($template['file']), $dirname, $trustDirname);
		$tplData = self::readTemplateFile($dirname, $trustDirname, $filename['trust']);
		if ($tplData == false)
			return false;
		$tplFile =& $tplHandler->create();
		$tplFile->set('tpl_refid', $module->get('mid'));
		$tplFile->set('tpl_lastimported', 0);
		$tplFile->set('tpl_lastmodified', time());
		$tplFile->set('tpl_type', (substr($filename['trust'], -4) == '.css') ? 'css' : 'module');
		$tplFile->set('tpl_source', $tplData);
		$tplFile->set('tpl_module', $dirname);
		$tplFile->set('tpl_tplset', 'default');
		$tplFile->set('tpl_file', $filename['public']);
		$tplFile->set('tpl_desc', isset($template['description']) ? $template['description'] : '');
		if ($tplHandler->insert($tplFile)) {
			$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_TPL_INSTALLED'), $filename['public']));
		} else {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_TPL_INSTALLED'), $filename['public']));
			return false;
		}
		return true;
	}

	/**
	 * uninstall all of module templates
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @param bool $defaultOnly
	 */
	public static function uninstallAllOfModuleTemplates(&$module, &$log, $defaultOnly = true) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$tplHandler =& Cosmoapi_Utils::getXoopsHandler('tplfile');
		$delTemplates =& $tplHandler->find($defaultOnly ? 'default' : null, null, $module->get('mid'));
		if (is_array($delTemplates) && count($delTemplates) > 0) {
			$xoopsTpl = new XoopsTpl();
			$xoopsTpl->clear_cache(null, 'mod_' . $dirname);
			foreach ($delTemplates as $tpl) {
				if (!$tplHandler->delete($tpl))
					$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_TPL_UNINSTALLED'), $tpl->get('tpl_file')));
			}
		}
	}

	/**
	 * install all of blocks
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function installAllOfBlocks(&$module, &$log) {
		$blocks =& $module->getInfo('blocks');
		if (is_array($blocks) && count($blocks) > 0) {
			foreach ($blocks as $block) {
				$newBlock =& self::createBlockByInfo($module, $block);
				self::installBlock($module, $newBlock, $block, $log);
			}
		}
		return true;
	}

	/**
	 * create block by info
	 *
	 * @param XoopsModule &$module
	 * @param string[] $block
	 * @return XoopsBlock
	 */
	public static function &createBlockByInfo(&$module, $block) {
		$dirname = $module->get('dirname');
		$visible = isset($block['visible']) ? $block['visible'] : (isset($block['visible_any']) ? $block['visible_any'] : 0);
		$filename = isset($block['template']) ? self::replaceDirname($block['template'], $dirname) : null;
		$blockHandler =& Cosmoapi_Utils::getXoopsHandler('block');
		$blockObj =& $blockHandler->create();
		$blockObj->set('mid', $module->get('mid'));
		$blockObj->set('options', isset($block['options']) ? $block['options'] : null);
		$blockObj->set('name', $block['name']);
		$blockObj->set('title', $block['name']);
		$blockObj->set('block_type', 'M');
		$blockObj->set('c_type', '1');
		$blockObj->set('isactive', 1);
		$blockObj->set('dirname', $dirname);
		$blockObj->set('func_file', $block['file']);
		$blockObj->set('show_func', 'cl::' . $block['class']);
		$blockObj->set('template', $filename['public']);
		$blockObj->set('last_modified', time());
		$blockObj->set('visible', $visible);
		$blockObj->set('func_num', intval($block['func_num']));
		return $blockObj;
	}

	/**
	 * install block
	 *
	 * @param XoopsModule &$module
	 * @param XoopsBlock &$blockObj
	 * @param string[] &$block
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function installBlock(&$module, &$blockObj, &$block, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$isNew = $blockObj->isNew();
		$blockHandler =& Cosmoapi_Utils::getXoopsHandler('block');
		$autoLink = isset($block['show_all_module']) ? $block['show_all_module'] : false;
		if (!$blockHandler->insert($blockObj, $autoLink)) {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_INSTALLED'), $blockObj->get('name')));
			return false;
		}
		$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_BLOCK_INSTALLED'), $blockObj->get('name')));
		self::installBlockTemplate($blockObj, $module, $log);
		if (!$isNew)
			return true;
		if ($autoLink) {
			$sql = sprintf('INSERT INTO `%s` (`block_id`, `module_id`) VALUES (%d, 0);', $blockHandler->db->prefix('block_module_link'), $blockObj->get('bid'));
			if (!$blockHandler->db->query($sql))
				$log->addWarning(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_COULD_NOT_LINK'), $blockObj->get('name')));
		}
		$gpermHandler =& Cosmoapi_Utils::getXoopsHandler('groupperm');
		$perm =& $gpermHandler->create();
		$perm->set('gperm_itemid', $blockObj->get('bid'));
		$perm->set('gperm_name', 'block_read');
		$perm->set('gperm_modid', 1);
		if (isset($block['visible_any']) && $block['visible_any']) {
			$memberHandler =& Cosmoapi_Utils::getXoopsHandler('member');
			$groups =& $memberHandler->getGroups();
			foreach ($groups as $group) {
				$perm->set('gperm_groupid', $group->get('groupid'));
				$perm->setNew();
				if (!$gpermHandler->insert($perm))
					$log->addWarning(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_PERM_COULD_NOT_SET'), $blockObj->get('name')));
			}
		} else {
			foreach (array(XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS) as $group) {
				$perm->set('gperm_groupid', $group);
				$perm->setNew();
				if (!$gpermHandler->insert($perm))
					$log->addWarning(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_PERM_SET'), $blockObj->get('name')));
			}
		}
		return true;
	}

	/**
	 * install block template
	 *
	 * @param XoopsBlock &$block
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function installBlockTemplate(&$block, &$module, &$log) {
		$dirname = $module->get('dirname');
		$trustDirname = $module->getInfo('trust_dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		if ($block->get('template') == null)
			return true;
		$info =& $module->getInfo('blocks');
		$filename = self::replaceDirname($info[$block->get('func_num')]['template'], $dirname, $trustDirname);
		$tplHandler =& Cosmoapi_Utils::getXoopsHandler('tplfile');
		$cri = new CriteriaCompo();
		$cri->add(new Criteria('tpl_type', 'block'));
		$cri->add(new Criteria('tpl_tplset', 'default'));
		$cri->add(new Criteria('tpl_module', $dirname));
		$cri->add(new Criteria('tpl_file', $filename['public']));
		$tpls =& $tplHandler->getObjects($cri);
		if (count($tpls) > 0) {
			$tplFile =& $tpls[0];
		} else {
			$tplFile =& $tplHandler->create();
			$tplFile->set('tpl_refid', $block->get('bid'));
			$tplFile->set('tpl_tplset', 'default');
			$tplFile->set('tpl_file', $filename['public']);
			$tplFile->set('tpl_module', $dirname);
			$tplFile->set('tpl_type', 'block');
			// $tplFile->set('tpl_desc', $block->get('description'));
			$tplFile->set('tpl_lastimported', 0);
		}
		$tplSource = self::readTemplateFile($dirname, $trustDirname, $filename['trust'], true);
		$tplFile->set('tpl_source', $tplSource);
		$tplFile->set('tpl_lastmodified', time());
		if (!$tplHandler->insert($tplFile)) {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_TPL_INSTALLED'), $filename['public']));
			return false;
		}
		$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_BLOCK_TPL_INSTALLED'), $filename['public']));
		return true;
	}

	/**
	 * uninstall all of blocks
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function uninstallAllOfBlocks(&$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$successFlag = true;
		$blockHandler =& Cosmoapi_Utils::getXoopsHandler('block');
		$gpermHandler =& Cosmoapi_Utils::getXoopsHandler('groupperm');
		$cri = new Criteria('mid', $module->get('mid'));
		$blocks =& $blockHandler->getObjectsDirectly($cri);
		foreach ($blocks as $block) {
			if ($blockHandler->delete($block)) {
				$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_BLOCK_UNINSTALLED'), $block->get('name')));
			} else {
				$log->addWarning(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_UNINSTALLED'), $block->get('name')));
				$successFlag = false;
			}
			$cri = new CriteriaCompo();
			$cri->add(new Criteria('gperm_name', 'block_read'));
			$cri->add(new Criteria('gperm_itemid', $block->get('bid')));
			$cri->add(new Criteria('gperm_modid', 1));
			if (!$gpermHandler->deleteAll($cri)) {
				$log->addWarning(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_PERM_DELETE'), $block->get('name')));
				$successFlag = false;
			}
		}
		return $successFlag;
	}

	/**
	 * smart update all of blocks
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 */
	public static function smartUpdateAllOfBlocks(&$module, &$log) {
		$dirname = $module->get('dirname');
		$fileReader = new Legacy_ModinfoX2FileReader($dirname);
		$dbReader = new Legacy_ModinfoX2DBReader($dirname);
		$blocks =& $dbReader->loadBlockInformations();
		$blocks->update($fileReader->loadBlockInformations());
		foreach ($blocks->mBlocks as $block) {
			switch ($block->mStatus) {
			case LEGACY_INSTALLINFO_STATUS_LOADED:
				self::updateBlockTemplateByInfo($block, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_UPDATED:
				self::updateBlockByInfo($block, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_NEW:
				self::installBlockByInfo($block, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_DELETED:
				self::uninstallBlockByFuncNum($block->mFuncNum, $module, $log);
				break;
			default:
				break;
			}
		}
	}

	/**
	 * update block template by info
	 *
	 * @param Legacy_BlockInformation &$info
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 */
	public static function updateBlockTemplateByInfo(&$info, &$module, &$log) {
		$dirname = $module->get('dirname');
		$blockHandler =& Cosmoapi_Utils::getModuleHandler('newblocks', 'legacy');
		$cri = new CriteriaCompo();
		$cri->add(new Criteria('dirname', $dirname));
		$cri->add(new Criteria('func_num', $info->mFuncNum));
		$blocks =& $blockHandler->getObjects($cri);
		foreach ($blocks as $block) {
			self::uninstallBlockTemplate($block, $module, $log, true);
			self::installBlockTemplate($block, $module, $log);
		}
	}

	/**
	 * update block by info
	 *
	 * @param Legacy_BlockInformation &$info
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 */
	public static function updateBlockByInfo(&$info, &$module, &$log) {
		$dirname = $module->get('dirname');
		$trustDirname = $module->getInfo('trust_dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$blockHandler =& Cosmoapi_Utils::getModuleHandler('newblocks', 'legacy');
		$cri = new CriteriaCompo();
		$cri->add(new Criteria('dirname', $dirname));
		$cri->add(new Criteria('func_num', $info->mFuncNum));
		$blocks =& $blockHandler->getObjects($cri);
		foreach ($blocks as $block) {
			$filename = self::replaceDirname($info->mTemplate, $dirname, $trustDirname);
			$block->set('options', $info->mOptions);
			$block->set('name', $info->mName);
			$block->set('func_file', $info->mFuncFile);
			$block->set('show_func', $info->mShowFunc);
			// $block->set('edit_func', $info->mEditFunc);
			$block->set('template', $filename['public']);
			if ($blockHandler->insert($block)) {
				$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_BLOCK_UPDATED'), $block->get('name')));
			} else {
				$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_UPDATED'), $block->get('name')));
			}
			self::uninstallBlockTemplate($block, $module, $log, true);
			self::installBlockTemplate($block, $module, $log);
		}
	}

	/**
	 * install block by info
	 *
	 * @param Legacy_BlockInformation &$info
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function installBlockByInfo(&$info, &$module, &$log) {
		$dirname = $module->get('dirname');
		$trustDirname = $module->getInfo('trust_dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$filename = self::replaceDirname($info->mTemplate, $dirname, $trustDirname);
		$blockHandler =& Cosmoapi_Utils::getXoopsHandler('block');
		$block =& $blockHandler->create();
		$block->set('mid', $module->get('mid'));
		$block->set('func_num', $info->mFuncNum);
		$block->set('options', $info->mOptions);
		$block->set('name', $info->mName);
		$block->set('title', $info->mName);
		$block->set('dirname', $dirname);
		$block->set('func_file', $info->mFuncFile);
		$block->set('show_func', $info->mShowFunc);
		// $block->set('edit_func', $info->mEditFunc);
		$block->set('template', $filename['public']);
		$block->set('block_type', 'M');
		$block->set('c_type', 1);
		if (!$blockHandler->insert($block)) {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_INSTALLED'), $block->get('name')));
			return false;
		}
		$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_BLOCK_INSTALLED'), $block->get('name')));
		self::installBlockTemplate($block, $module, $log);
		return true;
	}

	/**
	 * uninstall block by func number
	 *
	 * @param int $func_num
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function uninstallBlockByFuncNum($func_num, &$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$blockHandler =& Cosmoapi_Utils::getModuleHandler('newblocks', 'legacy');
		$cri = new CriteriaCompo();
		$cri->add(new Criteria('dirname', $dirname));
		$cri->add(new Criteria('func_num', $func_num));
		$blocks =& $blockHandler->getObjects($cri);
		$successFlag = true;
		foreach ($blocks as $block) {
			if ($blockHandler->delete($block)) {
				$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_BLOCK_UNINSTALLED'), $block->get('name')));
			} else {
				$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_BLOCK_UNINSTALLED'), $block->get('name')));
				$successFlag = false;
			}
		}
		return $successFlag;
	}

	/**
	 * uninstall block template
	 *
	 * @param XoopsBlock &$block
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @param bool $defaultOnly
	 * @return bool
	 */
	public static function uninstallBlockTemplate(&$block, &$module, &$log, $defaultOnly = false) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$tplHandler =& Cosmoapi_Utils::getXoopsHandler('tplfile');
		$delTemplates =& $tplHandler->find($defaultOnly ? 'default' : null, 'block', $module->get('mid'), $dirname, $block->get('template'));
		if (is_array($delTemplates) && count($delTemplates) > 0) {
			foreach ($delTemplates as $tpl) {
				if (!$tplHandler->delete($tpl))
					$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_TPL_UNINSTALLED'), $tpl->get('tpl_file')));
			}
		}
		$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_BLOCK_TPL_UNINSTALLED'), $block->get('template')));
		return true;
	}

	/**
	 * install all of configs
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function installAllOfConfigs(&$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$successFlag = true;
		$configHandler =& Cosmoapi_Utils::getXoopsHandler('config');
		$fileReader = new Legacy_ModinfoX2FileReader($dirname);
		$preferences = $fileReader->loadPreferenceInformations();
		foreach ($preferences->mPreferences as $info) {
			$config =& $configHandler->createConfig();
			$config->set('conf_modid', $module->get('mid'));
			$config->set('conf_catid', 0);
			$config->set('conf_name', $info->mName);
			$config->set('conf_title', $info->mTitle);
			$config->set('conf_desc', $info->mDescription);
			$config->set('conf_formtype', $info->mFormType);
			$config->set('conf_valuetype', $info->mValueType);
			$config->setConfValueForInput($info->mDefault);
			$config->set('conf_order', $info->mOrder);
			if (count($info->mOption->mOptions) > 0) {
				foreach ($info->mOption->mOptions as $opt) {
					$option = $configHandler->createConfigOption();
					$option->set('confop_name', $opt->mName);
					$option->set('confop_value', $opt->mValue);
					$config->setConfOptions($option);
					unset($option);
				}
			}
			if ($configHandler->insertConfig($config)) {
				$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_CONFIG_ADDED'), $config->get('conf_name')));
			} else {
				$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_CONFIG_ADDED'), $config->get('conf_name')));
				$successFlag = false;
			}
		}
		return $successFlag;
	}

	/**
	 * install config by info
	 *
	 * @param Legacy_PreferenceInformation &$info
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 */
	public static function installConfigByInfo(&$info, &$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$configHandler =& Cosmoapi_Utils::getXoopsHandler('config');
		$config =& $configHandler->createConfig();
		$config->set('conf_modid', $module->get('mid'));
		$config->set('conf_catid', 0);
		$config->set('conf_name', $info->mName);
		$config->set('conf_title', $info->mTitle);
		$config->set('conf_desc', $info->mDescription);
		$config->set('conf_formtype', $info->mFormType);
		$config->set('conf_valuetype', $info->mValueType);
		$config->setConfValueForInput($info->mDefault);
		$config->set('conf_order', $info->mOrder);
		if (count($info->mOption->mOptions) > 0) {
			foreach ($info->mOption->mOptions as $opt) {
				$option = $configHandler->createConfigOption();
				$option->set('confop_name', $opt->mName);
				$option->set('confop_value', $opt->mValue);
				$config->setConfOptions($option);
				unset($option);
			}
		}
		if ($configHandler->insertConfig($config)) {
			$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_CONFIG_ADDED'), $config->get('conf_name')));
		} else {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_CONFIG_ADDED'), $config->get('conf_name')));
		}
	}

	/**
	 * uninstall all of configs
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function uninstallAllOfConfigs(&$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		if ($module->get('hasconfig') == 0)
			return true;
		$configHandler =& Cosmoapi_Utils::getXoopsHandler('config');
		$configs =& $configHandler->getConfigs(new Criteria('conf_modid', $module->get('mid')));
		if (count($configs) == 0)
			return true;
		$sucessFlag = true;
		foreach ($configs as $config) {
			if ($configHandler->deleteConfig($config)) {
				$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_CONFIG_DELETED'), $config->get('conf_name')));
			} else {
				$log->addWarning(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_CONFIG_DELETED'), $config->get('conf_name')));
				$sucessFlag = false;
			}
		}
		return $sucessFlag;
	}

	/**
	 * uninstall config by order
	 *
	 * @param int $order
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 */
	public static function uninstallConfigByOrder($order, &$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$configHandler =& Cosmoapi_Utils::getXoopsHandler('config');
		$cri = new CriteriaCompo();
		$cri->add(new Criteria('conf_modid', $module->get('mid')));
		$cri->add(new Criteria('conf_catid', 0));
		$cri->add(new Criteria('conf_order', $order));
		$configs = $configHandler->getConfigs($cri);
		foreach ($configs as $config) {
			if ($configHandler->deleteConfig($config)) {
				$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_CONFIG_DELETED'), $config->get('conf_name')));
			} else {
				$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_CONFIG_DELETED'), $config->get('conf_name')));
			}
		}
	}

	/**
	 * smart update all of configs
	 *
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 */
	public static function smartUpdateAllOfConfigs(&$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$fileReader = new Legacy_ModinfoX2FileReader($dirname);
		$dbReader = new Legacy_ModinfoX2DBReader($dirname);
		$configs =& $dbReader->loadPreferenceInformations();
		$configs->update($fileReader->loadPreferenceInformations());
		foreach ($configs->mPreferences as $config) {
			switch ($config->mStatus) {
			case LEGACY_INSTALLINFO_STATUS_UPDATED:
				self::updateConfigByInfo($config, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_ORDER_UPDATED:
				self::updateConfigOrderByInfo($config, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_NEW:
				self::installConfigByInfo($config, $module, $log);
				break;
			case LEGACY_INSTALLINFO_STATUS_DELETED:
				self::uninstallConfigByOrder($config->mOrder, $module, $log);
				break;
			default:
				break;
			}
		}
	}

	/**
	 * update config by info
	 *
	 * @param Legacy_PreferenceInformation &$info
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function updateConfigByInfo(&$info, &$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$configHandler =& Cosmoapi_Utils::getXoopsHandler('config');
		$cri = new CriteriaCompo();
		$cri->add(new Criteria('conf_modid', $module->get('mid')));
		$cri->add(new Criteria('conf_catid', 0));
		$cri->add(new Criteria('conf_name', $info->mName));
		$configs =& $configHandler->getConfigs($cri);
		if (!(count($configs) > 0 && is_object($configs[0]))) {
			$log->addError(constant($constpref . '_INSTALL_ERROR_CONFIG_NOT_FOUND'));
			return false;
		}
		$config =& $configs[0];
		$config->set('conf_title', $info->mTitle);
		$config->set('conf_desc', $info->mDescription);
		if ($config->get('conf_formtype') != $info->mFormType && $config->get('conf_valuetype') != $info->mValueType) {
			$config->set('conf_formtype', $info->mFormType);
			$config->set('conf_valuetype', $info->mValueType);
			$config->setConfValueForInput($info->mDefault);
		} else {
			$config->set('conf_formtype', $info->mFormType);
			$config->set('conf_valuetype', $info->mValueType);
		}
		$config->set('conf_order', $info->mOrder);
		$options =& $configHandler->getConfigOptions(new Criteria('conf_id', $config->get('conf_id')));
		if (is_array($options)) {
			foreach ($options as $opt)
				$configHandler->_oHandler->delete($opt);
		}
		if (count($info->mOption->mOptions) > 0) {
			foreach ($info->mOption->mOptions as $opt) {
				$option =& $configHandler->createConfigOption();
				$option->set('confop_name', $opt->mName);
				$option->set('confop_value', $opt->mValue);
				$option->set('conf_id', $option->get('conf_id'));
				$config->setConfOptions($option);
				unset($option);
			}
		}
		if (!$configHandler->insertConfig($config)) {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_CONFIG_UPDATED'), $config->get('conf_name')));
			return false;
		}
		$log->addReport(XCube_Utils::formatString(constant($constpref . '_INSTALL_MSG_CONFIG_UPDATED'), $config->get('conf_name')));
		return true;
	}

	/**
	 * update config order by info
	 *
	 * @param Legacy_PreferenceInformation &$info
	 * @param XoopsModule &$module
	 * @param Legacy_ModuleInstallLog &$log
	 * @return bool
	 */
	public static function updateConfigOrderByInfo(&$info, &$module, &$log) {
		$dirname = $module->get('dirname');
		$constpref = '_MI_' . strtoupper($dirname);
		$configHandler =& Cosmoapi_Utils::getXoopsHandler('config');
		$cri = new CriteriaCompo();
		$cri->add(new Criteria('conf_modid', $module->get('mid')));
		$cri->add(new Criteria('conf_catid', 0));
		$cri->add(new Criteria('conf_name', $info->mName));
		$configs =& $configHandler->getConfigs($cri);
		if (!(count($configs) > 0 && is_object($configs[0]))) {
			$log->addError(constant($constpref . '_INSTALL_ERROR_CONFIG_NOT_FOUND'));
			return false;
		}
		$config =& $configs[0];
		$config->set('conf_order', $info->mOrder);
		if (!$configHandler->insertConfig($config)) {
			$log->addError(XCube_Utils::formatString(constant($constpref . '_INSTALL_ERROR_CONFIG_UPDATED'), $config->get('conf_name')));
			return false;
		}
		return true;
	}

}

