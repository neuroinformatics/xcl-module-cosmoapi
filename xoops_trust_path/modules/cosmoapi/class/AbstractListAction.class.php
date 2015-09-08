<?php

require_once XOOPS_ROOT_PATH . '/core/XCube_PageNavigator.class.php';

/**
 * abstract list action
 */
abstract class Cosmoapi_AbstractListAction extends Cosmoapi_AbstractAction {

	/**
	 * xoops objects
	 * @var XoopsSimpleObject[]
	 */
	public $mObjects = null;

	/**
	 * action filter form
	 * @var {Trustdirname}_AbstractFilterForm
	 */
	public $mFilter = null;

	/**
	 * get object handler
	 * 
	 * @return &XoopsObjectGenericHandler
	 */
	protected function &_getHandler() {
	}

	/**
	 * get action name
	 * 
	 * @return string
	 */
	protected function _getActionName() {
		return _LIST;
	}

	/**
	 * get filter form
	 * 
	 * @return &{Trustdirname}_AbstractFilterForm
	 */
	protected function &_getFilterForm() {
	}

	/**
	 * get base url
	 * 
	 * @return string
	 */
	protected function _getBaseUrl() {
	}

	/**
	 * get page navigation
	 * 
	 * @return &XCube_PageNavigator
	 */
	protected function &_getPageNavi() {
		$navi = new XCube_PageNavigator($this->_getBaseUrl(), XCUBE_PAGENAVI_START);
		return $navi;
	}

	/**
	 * get default view
	 * 
	 * @return Enum
	 */
	public function getDefaultView() {
		$this->mFilter =& $this->_getFilterForm();
		$this->mFilter->fetch();
		$handler =& $this->_getHandler();
		$this->mObjects =& $handler->getObjects($this->mFilter->getCriteria());
		return $this->_getFrameViewStatus('INDEX');
	}

	/**
	 * execute
	 * 
	 * @return Enum
	 */
	public function execute() {
		return $this->getDefaultView();
	}

}

