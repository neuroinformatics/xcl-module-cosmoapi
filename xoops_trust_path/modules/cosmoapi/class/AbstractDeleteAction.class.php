<?php

require_once dirname(__FILE__) . '/AbstractEditAction.class.php';

/**
 * abstract delete action
 */
abstract class Cosmoapi_AbstractDeleteAction extends Cosmoapi_AbstractEditAction {

	/**
	 * check whether form is enable to create
	 * 
	 * @return bool
	 */
	protected function _isEnableCreate() {
		return false;
	}

	/**
	 * get action name
	 * 
	 * @return string
	 */
	protected function _getActionName() {
		return _DELETE;
	}

	/**
	 * prepare
	 * 
	 * @return bool
	 */
	public function prepare() {
		return parent::prepare() && is_object($this->mObject);
	}

	/**
	 * do execute
	 * 
	 * @return Enum
	 */
	protected function _doExecute() {
		if(!$this->mObjectHandler->delete($this->mObject))
			return $this->_getFrameViewStatus('ERROR');
		return $this->_getFrameViewStatus('SUCCESS');
	}

}

