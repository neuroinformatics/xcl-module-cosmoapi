<?php

if (!defined('XOOPS_ROOT_PATH')) exit();

$mydirname = realpath(XOOPS_ROOT_PATH) == realpath(dirname(dirname(__FILE__))) ? '' : basename(dirname(dirname(__FILE__)));

if (!class_exists('NIJC_AutoTemplateUpdateBase')) {

class NIJC_AutoTemplateUpdateBase extends XCube_ActionFilter {

	const TEMPLATE_SET = 'default';

	/**
	 * dirname
	 * @var string
	 */
	public $mDirname = '';

	/**
	 * module templates
	 * @var array
	 */
	protected $mTemplates = array();

	/**
	 * prepare
	 *
	 * @param string $dirname
	 */
	public static function prepare($dirname) {
		$root =& XCube_Root::getSingleton();
		$instance = new self($root->mController);
		$instance->mDirname = $dirname;
		$root->mController->addActionFilter($instance);
	}

	/**
	 * pre block filter
	 */
	public function preBlockFilter() {
		require_once XOOPS_TRUST_PATH.'/libs/altsys/include/tpls_functions.php';
		$this->_getModuleTemplates();
		$dirnames = $this->mDirname == '' ? $this->_getActiveDirnames() : array($this->mDirname);
		foreach ($dirnames as $dirname) {
			if (!isset($this->mTemplates[$dirname]) || empty($this->mTemplates[$dirname]))
				continue;
			$trustDirname = $this->_getTrustDirname($dirname);
			if (empty($trustDirname))
				$path = XOOPS_ROOT_PATH . '/modules/' . $dirname . '/templates';
			else
				$path = XOOPS_TRUST_PATH . ($trustDirname == 'altsys' ? '/libs/' : '/modules/') . $trustDirname . '/templates' ;
			$this->_updateTemplates($dirname, $trustDirname, $path);
		}
	}

	/**
	 * get active dirnames
	 *
	 * @return string[]
	 */
	protected function _getActiveDirnames() {
		$moduleHandler =& xoops_gethandler('module');
		$criteria = new Criteria('isactive', 1);
		$moduleNames =& $moduleHandler->getList($criteria, true);
		return array_keys($moduleNames);
	}

	/**
	 * get trust dirname
	 *
	 * @param string $dirname
	 * @return string
	 */
	protected function _getTrustDirname($dirname) {
		if (file_exists($fpath = XOOPS_ROOT_PATH . '/modules/' . $dirname . '/mytrustdirname.php')) {
			include $fpath;
			return $mytrustdirname;
		}
		return Legacy_Utils::getTrustDirnameByDirname($dirname);
	}

	/**
	 * get module templates
	 */
	protected function _getModuleTemplates() {
 		$templates = array();
		$root =& XCube_Root::getSingleton();
		$db =& $root->mController->getDB();
		$sql = sprintf('SELECT `tpl_module`, `tpl_file`, `tpl_lastmodified` FROM `%s` WHERE `tpl_tplset`=%s', $db->prefix('tplfile'), $db->quoteString(self::TEMPLATE_SET));
		if ($this->mDirname != '')
			$sql .= sprintf(' AND `tpl_module`=%s', $db->quoteString($this->mDirname));
		if (!($res = $db->query($sql)))
			return false;
		while ($row = $db->fetchArray($res)) {
			$dirname = $row['tpl_module'];
			$file = $row['tpl_file'];
			$lastmodified = $row['tpl_lastmodified'];
			$templates[$dirname][$file] = $lastmodified;
		}
		$db->freeRecordSet($res);
		$this->mTemplates = $templates;
	}

	/**
	 * update module templates recursively
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 * @param string $path
	 */
	protected function _updateTemplates($dirname, $trustDirname, $path) {
		if ($handler = @opendir($path . '/')) {
			while (($fname = readdir($handler)) !== false) {
				if (in_array($fname, array('.', '..')))
					continue;
				$file_path = $path . '/' . $fname;
				if (is_file($file_path))
					$this->_updateTemplateFile($dirname, $trustDirname, $path, $fname);
				if (is_dir($file_path) && $fname == 'blocks')
					$this->_updateTemplates($dirname, $trustDirname, $file_path);
			}
			closedir($handler);
		}
	}

	/**
	 * update module template file
	 *
	 * @param string $dirname
	 * @param string $trustDirname
	 * @param string $path
	 * @param string $fname
	 */
	protected function _updateTemplateFile($dirname, $trustDirname, $path, $fname) {
		$fpath = $path . '/' . $fname;
		$mtime = intval(@filemtime($fpath));
		if (!empty($trustDirname) && strpos($fname, $trustDirname . '_') === 0)
			$tpl_file = preg_replace('/^'. $trustDirname . '/', $dirname, $fname);
		else if (strpos($fname, $dirname . '_') === 0)
			$tpl_file = $fname;
		else
			$tpl_file = $dirname . '_' . $fname;
		if (!isset($this->mTemplates[$dirname][$tpl_file]))
			return;
		if ($this->mTemplates[$dirname][$tpl_file] >= $mtime)
			return;
		tplsadmin_import_data(self::TEMPLATE_SET, $tpl_file, file_get_contents($fpath), $mtime);
	}

}

}

NIJC_AutoTemplateUpdateBase::prepare($mydirname);

