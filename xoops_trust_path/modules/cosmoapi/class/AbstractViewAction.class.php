<?php

/**
 * abstract view action.
 */
abstract class Cosmoapi_AbstractViewAction extends Cosmoapi_AbstractAction
{
    /**
     * xoops object.
     *
     * @var XoopsSimpleObject
     */
    public $mObject = null;

    /**
     * xoops object handler.
     *
     * @var XoopsObjectGenericHandler
     */
    public $mObjectHandler = null;

    /**
     *  get id.
     * 
     * @return int
     */
    protected function _getId()
    {
        $req = $this->mRoot->mContext->mRequest;
        $dataId = $req->getRequest(_REQUESTED_DATA_ID);

        return isset($dataId) ? intval($dataId) : intval($req->getRequest($this->_getHandler()->mPrimary));
    }

    /**
     * get object handler.
     * 
     * @return &XoopsObjectGenericHandler
     */
    protected function &_getHandler()
    {
    }

    /**
     * get actin name.
     * 
     * @return string
     */
    protected function _getActionName()
    {
        return _VIEW;
    }

    /**
     * set object.
     * 
     * @param void
     */
    protected function _setupObject()
    {
        $id = $this->_getId();
        $this->mObjectHandler = &$this->_getHandler();
        $this->mObject = &$this->mObjectHandler->get($id);
    }

    /**
     * prepare.
     * 
     * @return bool
     */
    public function prepare()
    {
        $this->_setupObject();

        return is_object($this->mObject);
    }

    /**
     * getDefaultView.
     * 
     * @return Enum
     */
    public function getDefaultView()
    {
        if ($this->mObject == null) {
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
}
