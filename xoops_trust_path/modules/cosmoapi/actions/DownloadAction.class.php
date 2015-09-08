<?php

/**
 * item download action
 */
class Cosmoapi_DownloadAction extends Cosmoapi_AbstractAction {

	/**
	 * module name
	 * @var string
	 */
	protected $mDirname = '';

	/**
	 * item id
	 * @var int
	 */
	protected $mItemId = 0;

	/**
	 * data handler
	 * @var {TrustDirname}_DataHandler
	 */
	protected $mHandler = null;

	/**
	 * file path
	 * @var {TrustDirname}_DataObject
	 */
	protected $mFilePath = null;

	/**
	 * get id
	 *
	 * @return int
	 */
	protected function _getId() {
		$req = $this->mRoot->mContext->mRequest;
		$dataId = $req->getRequest(_REQUESTED_DATA_ID);
		$dataName = $req->getRequest(_REQUESTED_DATA_NAME);
		if (isset($_SERVER['PATH_INFO']) && preg_match('/^\/([a-z0-9]+)(?:\/([a-z0-9][a-zA-Z0-9\._\-]*))?(?:\/([a-z0-9]+))?$/', $_SERVER['PATH_INFO'], $matches)) {
			if (isset($matches[2]))
				$dataName = $matches[2];
			if (isset($matches[3]))
				$dataId = $matches[3];
		}
		$this->mDirname = trim($dataName);
		$this->mItemId = intval($dataId);
		return $this->mItemId;
	}

	/**
	 * get default view
	 *
	 * @return Enum
	 */
	public function getDefaultView() {
		if (!$this->mRoot->mContext->mUser->mIdentity->isAuthenticated()) {
			return $this->_getFrameViewStatus('ERROR');
		}
		$this->mHandler =& Cosmoapi_Utils::getTrustModuleHandler('data', $this->mAsset->mTrustDirname);
		$this->_getId();
		if (!$this->mHandler->setDirname($this->mDirname))
			return $this->_getFrameViewStatus('ERROR');
		$this->mFilePath = $this->mHandler->getItemFilePath($this->mItemId);
		if (!$this->mFilePath)
			return $this->_getFrameViewStatus('ERROR');
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
		self::_clearObFilters();
                header('Content-Type: ' . $this->_getFileMimeType());
                header('Content-Length: ' . filesize($this->mFilePath));
		set_time_limit(0);
		$fp = @fopen($this->mFilePath, 'rb');
		while (!feof($fp)) {
			print(@fread($fp, 1024*8));
			ob_flush();
			flush();
		}
		fclose($fp);
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

	protected function _getFileMimeType() {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $this->mFilePath);
		finfo_close($finfo);
		return $mime;
	}
}

