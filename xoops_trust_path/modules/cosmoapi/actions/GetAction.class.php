<?php

/**
 * get data action.
 */
class Cosmoapi_GetAction extends Cosmoapi_AbstractAction
{
    /**
     * module name.
     *
     * @var string
     */
    protected $mDirname = '';

    /**
     * data id.
     *
     * @var int
     */
    protected $mDataId = 0;

    /**
     * data handler.
     *
     * @var {TrustDirname}_DataHandler
     */
    protected $mHandler = null;

    /**
     * data object.
     *
     * @var {TrustDirname}_DataObject
     */
    protected $mObject = null;

    /**
     * get id.
     *
     * @return int
     */
    protected function _getId()
    {
        $req = $this->mRoot->mContext->mRequest;
        $dataId = $req->getRequest(_REQUESTED_DATA_ID);
        $dataName = $req->getRequest(_REQUESTED_DATA_NAME);
        if (isset($_SERVER['PATH_INFO']) && preg_match('/^\/([a-z0-9]+)(?:\/([a-z0-9][a-zA-Z0-9\._\-]*))?(?:\/([a-z0-9]+))?$/', $_SERVER['PATH_INFO'], $matches)) {
            if (isset($matches[2])) {
                $dataName = $matches[2];
            }
            if (isset($matches[3])) {
                $dataId = $matches[3];
            }
        }
        $this->mDirname = trim($dataName);
        $this->mDataId = intval($dataId);

        return $this->mDataId;
    }

    /**
     * get default view.
     *
     * @return Enum
     */
    public function getDefaultView()
    {
        if (!$this->mRoot->mContext->mUser->mIdentity->isAuthenticated()) {
            return $this->_getFrameViewStatus('ERROR');
        }
        $this->mHandler = &Cosmoapi_Utils::getTrustModuleHandler('data', $this->mAsset->mTrustDirname);
        $this->_getId();
        if (!$this->mHandler->setDirname($this->mDirname)) {
            return $this->_getFrameViewStatus('ERROR');
        }
        $this->mObject = &$this->mHandler->get($this->mDataId);
        if (!$this->mObject) {
            return $this->_getFrameViewStatus('ERROR');
        }

        return $this->_getFrameViewStatus('SUCCESS');
    }

    /**
     * execute.
     * 
     * @return Enum
     */
    public function execute()
    {
        return $this->getDefaultView();
    }

    /**
     * execute view success.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewSuccess(&$render)
    {
        $render->setTemplateName($this->mAsset->mDirname.'_data.xml');
        $render->setAttribute('object', $this->mObject);
        $renderSystem = &$this->mModule->getRenderSystem();
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
     * execute view error.
     *
     * @param XCube_RenderTarget &$render
     */
    public function executeViewError(&$render)
    {
        self::_clearObFilters();
        $error = 'HTTP/1.0 403 Forbidden';
        header($error);
        echo $error;
        register_shutdown_function(array($this, 'onShutdown'));
        ob_start();
        exit();
    }

    /**
     * on shutdown callback handler.
     */
    public function onShutdown()
    {
        self::_clearObFilters();
    }

    /**
     * clear ob filters.
     */
    protected static function _clearObFilters()
    {
        $handlers = ob_list_handlers();
        while (!empty($handlers)) {
            ob_end_clean();
            $handlers = ob_list_handlers();
        }
    }
}
