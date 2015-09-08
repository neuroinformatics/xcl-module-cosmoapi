<?php

/**
 * index action
 */
class Cosmoapi_IndexAction extends Cosmoapi_AbstractAction {

	/**
	 * get default view
	 * 
	 * @return  Enum
	 */
	public function getDefaultView() {
		return $this->_getFrameViewStatus('INDEX');
	}

	public function executeViewIndex(&$render) {
		$prefix = '_MD_' . strtoupper($this->mAsset->mDirname);
		$root =& XCube_Root::getSingleton();
		$root->mController->executeRedirect(XOOPS_URL . '/', 3, constant($prefix . '_ERROR_NO_PERMISSION'));
        }

}

