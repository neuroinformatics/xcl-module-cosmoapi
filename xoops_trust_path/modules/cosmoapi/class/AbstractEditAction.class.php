<?php

/**
 * abstract edit action
 */
abstract class Cosmoapi_AbstractEditAction extends Cosmoapi_AbstractAction {

	/**
	 * xoops object
	 * @var XoopsSimpleObject
	 */
	public $mObject = null;

	/**
	 * xoops object
	 * @var XoopsObjectGenericHandler
	 */
	public $mObjectHandler = null;

	/**
	 * action form
	 * @var XCube_ActionForm
	 */
	public $mActionForm = null;

	/**
	 * get id
	 * 
	 * @return int
	 */
	protected function _getId() {
		$req = $this->mRoot->mContext->mRequest;
		$dataId = $req->getRequest(_REQUESTED_DATA_ID);
		return isset($dataId) ? intval($dataId) : intval($req->getRequest($this->_getHandler()->mPrimary));
	}

	/**
	 * get handler
	 * 
	 * @return XoopsObjectGenericHandler
	 */
	protected function &_getHandler() {
	}

	/**
	 * get action name
	 * 
	 * @return string
	 */
	protected function _getActionName() {
		return _EDIT;
	}

	/**
	 * setup action form
	 */
	protected function _setupActionForm() {
	}

	/**
	 * setup object
	 */
	protected function _setupObject() {
		$id = $this->_getId();
		$this->mObjectHandler =& $this->_getHandler();
		$this->mObject =& $this->mObjectHandler->get($id);
		if ($this->mObject == null && $this->_isEnableCreate())
			$this->mObject =& $this->mObjectHandler->create();
	}

	/**
	 * check whether form is enable to create
	 * 
	 * @return bool
	 */
	protected function _isEnableCreate() {
		return true;
	}

	/**
	 * prepare
	 * 
	 * @return bool
	 */
	public function prepare() {
		$this->_setupObject();
		$this->_setupActionForm();
		return true;
	}

	/**
	 * get default view
	 * 
	 * @return Enum
	 */
	public function getDefaultView() {
		if ($this->mObject == null)
			return $this->_getFrameViewStatus('ERROR');
		$this->mActionForm->load($this->mObject);
		return $this->_getFrameViewStatus('INPUT');
	}

	/**
	 * execute
	 * 
	 * @return Enum
	 */
	public function execute() {
		if ($this->mObject == null)
			return $this->_getFrameViewStatus('ERROR');
		if ($this->mRoot->mContext->mRequest->getRequest('_form_control_cancel') != null)
			return $this->_getFrameViewStatus('CANCEL');
		$this->mActionForm->load($this->mObject);
		$this->mActionForm->fetch();
		$this->mActionForm->validate();
		if ($this->mActionForm->hasError())
			return $this->_getFrameViewStatus('INPUT');
		$this->mActionForm->update($this->mObject);
		return $this->_doExecute();
	}

	/**
	 * do execute
	 * 
	 * @return Enum
	 */
	protected function _doExecute() {
		if($this->mObjectHandler->insert($this->mObject))
			return $this->_getFrameViewStatus('SUCCESS');
		return $this->_getFrameViewStatus('ERROR');
	}

}

