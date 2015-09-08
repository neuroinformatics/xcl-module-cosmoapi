<?php

/**
 * Admin IndexAction
 */
class Cosmoapi_Admin_IndexAction extends Cosmoapi_AbstractAction {

	/**
	 * getDefaultView
	 *
	 * @return Enum
	 */
	public function getDefaultView() {
		return $this->_getFrameViewStatus('SUCCESS');
	}

	/**
	 * executeViewSuccess
	 *
	 * @param XCube_RenderTarget &$render
	 */
	public function executeViewSuccess(&$render) {
		$render->setTemplateName('admin.html');
		$render->setAttribute('adminMenu', $this->mModule->getAdminMenu());

		$mHandler =& xoops_gethandler('module');
		if ($mHandler->getByDirname('altsys')) {
			$dirname = $this->mModule->mXoopsModule->get('dirname');
			$altsysAdminUrl = XOOPS_URL . '/modules/altsys/admin/index.php';
			$altsysMenu = array(
				array(
					'title' => _MI_ALTSYS_MENU_MYBLOCKSADMIN,
					'link' => $altsysAdminUrl . '?mode=admin&lib=altsys&page=myblocksadmin&dirname='.$dirname
				),
				array(
					'title' => _MI_ALTSYS_MENU_MYTPLSADMIN,
					'link' => $altsysAdminUrl . '?mode=admin&lib=altsys&page=mytplsadmin&dirname='.$dirname
				),
				array(
					'title' => _MI_ALTSYS_MENU_MYLANGADMIN,
					'link' => $altsysAdminUrl . '?mode=admin&lib=altsys&page=mylangadmin&dirname='.$dirname
				)
			);
			$render->setAttribute('altsysMenu', $altsysMenu);
		}
	}
}

