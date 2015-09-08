<?php

/**
 * login action
 */
class Cosmoapi_LoginAction extends Cosmoapi_AbstractAction {

	/**
	 * module name
	 * @var string
	 */
	protected $mDirname = '';

	protected $mResult = true;

	/**
	 * get default view
	 *
	 * @return Enum
	 */
	public function getDefaultView() {
		if (!$this->mRoot->mContext->mUser->mIdentity->isAuthenticated())
			$this->mResult = $this->_checkLogin();
		return $this->_getFrameViewStatus('SUCCESS');
	}

	/**
	 * execute
	 * 
	 * @return Enum
	 */
	public function execute() {
		return $this->getDefaultView();
	}

	/**
	 * execute view success
	 *
	 * @param XCube_RenderTarget &$render
	 */
	public function executeViewSuccess(&$render) {
		$render->setTemplateName($this->mAsset->mDirname . '_login.xml');
		$render->setAttribute('result', $this->mResult);
		$renderSystem =& $this->mModule->getRenderSystem();
		$renderSystem->render($render);
		$xml = $render->getResult();
		self::_clearObFilters();
		header('Content-Type: application/xml');
		echo $xml;
		register_shutdown_function(array($this, 'onShutdown'));
		ob_start();
		exit();
	}

	/**
	 * execute view error
	 *
	 * @param XCube_RenderTarget &$render
	 */
	public function executeViewError(&$render) {
		self::_clearObFilters();
		$error = 'HTTP/1.0 403 Forbidden';
		header($error);
		echo $error;
		register_shutdown_function(array($this, 'onShutdown'));
		ob_start();
		exit();
	}

	/**
	 * on shutdown callback handler
	 */
	public function onShutdown() {
		self::_clearObFilters();
	}

	/**
	 * clear ob filters
	 */
	protected static function _clearObFilters() {
		$handlers = ob_list_handlers();
		while (!empty($handlers)) {
			ob_end_clean();
			$handlers = ob_list_handlers();
		}
	}

	protected function _checkLogin() {
		$this->mRoot->mController->mCheckLogin->call(new XCube_Ref($this->mRoot->mContext->mXoopsUser));
		$this->mRoot->mLanguageManager->loadModuleMessageCatalog('legacy');
		if (is_object($this->mRoot->mContext->mXoopsUser)) {
			$notification_handler =& xoops_gethandler('notification');
			$notification_handler->doLoginMaintenance($this->mRoot->mContext->mXoopsUser->get('uid'));
			XCube_DelegateUtils::call("Site.CheckLogin.Success", new XCube_Ref($this->mRoot->mContext->mXoopsUser));
		} else {
			XCube_DelegateUtils::call("Site.CheckLogin.Fail", new XCube_Ref($this->mRoot->mContext->mXoopsUser));
			return false;
		}
		return true;
	}
}

