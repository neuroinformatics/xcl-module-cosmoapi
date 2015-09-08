<?php

require_once XOOPS_MODULE_PATH . '/legacy/class/ActionFrame.class.php';
require_once XOOPS_MODULE_PATH . '/legacy/admin/actions/HelpAction.class.php';

/**
 * Admin HelpAction
 */
class Cosmoapi_Admin_HelpAction extends Cosmoapi_AbstractAction {

	private $_mContents = '';
	private $_mErrorMessage = '';
	private $_mIsImage = false;

	/**
	 * getDefaultView
	 * 
	 * @return Enum
	 */
	public function getDefaultView() {
		$prefix = strtoupper($this->mAsset->mTrustDirname);

		$this->_mIsImage = xoops_getrequest('type') ? xoops_getrequest('type') == 'image' : false;
		if ($this->_mIsImage)
			return $this->_getDefaultViewForImage();


		$language = $this->mRoot->mContext->getXoopsConfig('language');
		$helpfile = xoops_getrequest('file') ? xoops_getrequest('file') : $this->mModule->mXoopsModule->getHelp();

		// file check
		$template_dir = XOOPS_MODULE_PATH . '/' . $this->mAsset->mDirname . '/language/' . $language . '/help';
		if (!file_exists($template_dir . '/' . $helpfile)) {
			$template_dir = XOOPS_MODULE_PATH . '/' . $this->mAsset->mDirname . '/language/english/help';
			if (!file_exists($template_dir . '/' . $helpfile)) {
				$template_dir = XOOPS_TRUST_PATH . '/modules/' . $this->mAsset->mTrustDirname . '/language/' . $language . '/help';
				if (!file_exists($template_dir . '/' . $helpfile)) {
					$template_dir = XOOPS_TRUST_PATH . '/modules/' . $this->mAsset->mTrustDirname . '/language/english/help';
					if (!file_exists($template_dir . '/' . $helpfile)) {
						$this->_mErrorMessage = _AD_LEGACY_ERROR_NO_HELP_FILE;
						return $this->_getFrameViewStatus('ERROR');
					}
				}
			}
		}

		$this->mRoot->mContext->setAttribute('legacy_help_dirname', $this->mAsset->mDirname);
		$this->mRoot->mContext->setAttribute('legacy_help_trust_dirname', $this->mAsset->mTrustDirname);

		// Smarty
		$smarty = new Legacy_HelpSmarty();
		$smarty->unregister_modifier('helpurl');
		$smarty->unregister_modifier('helpimage');
		$smarty->register_modifier('helpurl', __CLASS__ . '::smartyModifierHelpurl');
		$smarty->register_modifier('helpimage', __CLASS__ . '::smartyModifierHelpimage');

		$smarty->setDirname($this->mAsset->mDirname);
		$smarty->template_dir = $template_dir;

		$smarty->assign('mydirname', $this->mAsset->mDirname);
		$smarty->assign('mytrustdirname', $this->mAsset->mTrustDirname);

		$this->_mContents = $smarty->fetch('file:' . $helpfile);
		return $this->_getFrameViewStatus('SUCCESS');
	}

	/**
	 * get default view for image
	 *
	 * @return Enum
	 */
	private function _getDefaultViewForImage() {
		$language = $this->mRoot->mContext->getXoopsConfig('language');
		$file = xoops_getrequest('file') ? xoops_getrequest('file') : false;
		if (!$file)
			return $this->_getFrameViewStatus('ERROR');
		$imageDir = XOOPS_TRUST_PATH . '/modules/' . $this->mAsset->mTrustDirname . '/language/' . $language . '/help/images';
		if (!file_exists($imageDir . '/' . $file)) {
			$imageDir = XOOPS_TRUST_PATH . '/modules/' . $this->mAsset->mTrustDirname . '/language/english/help/images';
			if (!file_exists($imageDir . '/' . $file))
				return $this->_getFrameViewStatus('ERROR');
		}
		$this->_mContents = $imageDir . '/' . $file;
		return $this->_getFrameViewStatus('SUCCESS');
	}

	/**
	 * executeViewSuccess
	 * 
	 * @param XCube_RenderTarget &$render
	 */
	public function executeViewSuccess(&$render) {
		if ($this->_mIsImage) {
			self::_showImage($this->_mContents);
		}
		$render->setTemplateName('help.html');
		$render->setAttribute('module', $this->mModule);
		$render->setAttribute('contents', $this->_mContents);
	}

	/**
	 * executeViewError
	 * 
	 * @param XCube_RenderTarget &$render
	 */
	public function executeViewError(&$render) {
		if ($this->_mIsImage)
			self::_error404();
		$url = XOOPS_URL . '/modules/' . $this->mAsset->mDirname . '/' . $this->mModule->mXoopsModule->getInfo('adminindex');
		$this->mRoot->mController->executeRedirect($url, 1, $this->_mErrorMessage);
	}

	/**
	 * helpurl - smarty modifier
	 *
	 * @param string $file
	 * @param string $dirname
	 * @return string	
	 */
	public static function smartyModifierHelpurl($file, $dirname = null) {
		$root =& XCube_Root::getSingleton();
		$language = $root->mContext->getXoopsConfig('language');
		$dirname = $root->mContext->getAttribute('legacy_help_dirname');
		$url = XOOPS_MODULE_URL . '/' . $dirname . '/admin/index.php?action=Help&amp;file=' . $file;
		return $url;
	}

	/**
	 * helpimage - smarty modifier
	 *
	 * @param string $file
	 * @return string	
	 */
	public static function smartyModifierHelpimage($file) {
		$root =& XCube_Root::getSingleton();
		$language = $root->mContext->getXoopsConfig('language');
		$dirname = $root->mContext->getAttribute('legacy_help_dirname');
		$trustDirname = $root->mContext->getAttribute('legacy_help_trust_dirname');
		$path = '/' . $dirname . '/language/' . $language . '/help/images/' . $file;
		if (!file_exists(XOOPS_MODULE_PATH . $path) && $language != 'english')
			$path = '/' . $dirname . '/language/english/help/images/' . $file;
		if (file_exists(XOOPS_MODULE_PATH . $path))
			return XOOPS_MODULE_URL . $path;
		// check trust file
		$path = '/' . $trustDirname . '/language/' . $language . '/help/images/' . $file;
		if (!file_exists(XOOPS_TRUST_PATH . '/modules' . $path) && $language != 'english') {
			$path = '/' . $trustDirname . '/language/english/help/images/' . $file;
		}
		return XOOPS_MODULE_URL . '/' . $dirname . '/admin/index.php?action=Help&amp;type=image&amp;file=' . $file;
	}

	/**
	 * show image
	 *
	 * @param resource $im
	 */
	private static function _showImage($fpath) {
		$info = @getimagesize($fpath);
		if ($info === false)
			self::_error404();
		self::_prepareImage();
		$cache_limit = 3600 ;
		session_cache_limiter('public');
		header('Expires: ' . date('r', intval(time() / $cache_limit) * $cache_limit + $cache_limit));
		header('Cache-Control: public, max-age=' . $cache_limit);
		header('Last-Modified: ' . date('r', intval(time() / $cache_limit) * $cache_limit));
		header('Content-Type: ' . $info['mime']);
		readfile($fpath);
		self::_cleanupImage();
	}

	/**
	 * output 404 Not Found
	 */
	private static function _error404() {
		self::_prepareImage();
		$error = 'HTTP/1.0 404 Not Found';
		header($error);
		echo $error;
		self::_cleanupImage();
	}

	/**
	 * prepare
	 */
	private static function _prepareImage() {
		self::_clearObFilters();
	}

	/**
	 * cleanup
	 */
	private static function _cleanupImage() {
		register_shutdown_function(array(__CLASS__, 'onShutdownImage'));
		ob_start();
		exit();
	}

	/**
	 * clear ob filters
	 */
	private static function _clearObFilters() {
		$handlers = ob_list_handlers();
		while (!empty($handlers)) {
			ob_end_clean();
			$handlers = ob_list_handlers();
		}
	}

	/**
	 * on shutdown callback handler for image handling
	 */
	public static function onShutdownImage() {
		self::_clearObFilters();
	}

}

