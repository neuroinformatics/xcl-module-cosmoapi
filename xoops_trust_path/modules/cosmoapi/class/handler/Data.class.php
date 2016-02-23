<?php

class Cosmoapi_DataObject {
	public $mDirname;
	public $mLabelId;
	public $mLabel;
	public $mRegDate;
	public $mUserIds;
	public $mAuthor;
	public $mKeywords;
	public $mViews;
	public $mComponents;
	public $mItems;

	public function __construct($dirname) {
		$this->mDirname = $dirname;
	}

	public function setArray($row) {
		if ($row === false)
			return false;
		$this->mLabelId = intval($row['label_id']);
		$this->mLabel = $row['label'];
		$this->mRegDate = $row['reg_date'];
		$this->mUserIds = array_map('intval', explode(',', $row['users']));
		$this->mAuthor = intval($row['author']);
		$this->mViews = intval($row['views']);
		$this->_setKeywords($row['keyword']);
		$this->_setComponents();
		$this->_setItems();
		return true;
	}

	protected function _setKeywords($keyword) {
		$kwHandler =& Cosmoapi_Utils::getTrustModuleHandler('keyword', COSMOAPI_TRUST_DIRNAME);
		$kwHandler->setDirname($this->mDirname);
		$keywords = explode(',', $keyword);
		$this->mKeywords = array();
		foreach ($keywords as $keyword) {
			$kw_id = intval(preg_replace('/\[([0-9]+)\]/', '$1', $keyword));
			if ($kw_id != 0)
				$this->mKeywords[$kw_id] = $kwHandler->get($kw_id);
		}
	}

	protected function _setComponents() {
		$compHandler =& Cosmoapi_Utils::getTrustModuleHandler('component', COSMOAPI_TRUST_DIRNAME);
		$compHandler->setDirname($this->mDirname);
		$this->mComponents = $compHandler->getListByLabelId($this->mLabelId);
	}

	protected function _setItems() {
		$itemHandler =& Cosmoapi_Utils::getTrustModuleHandler('item', COSMOAPI_TRUST_DIRNAME);
		$itemHandler->setDirname($this->mDirname);
		$this->mItems = $itemHandler->getListByLabelId($this->mLabelId);
	}
}

class Cosmoapi_DataHandler extends XoopsObjectHandler {

	private $mDirname;
	private $mKeywords;

	public function setDirname($dirname) {
		if (!preg_match('/^newdb.*$/', $dirname))
			return false;
  		$moduleHandler =& xoops_gethandler('module'); 
  		$moduleObj =& $moduleHandler->getByDirname($dirname); 
		if (!$moduleObj)
			return false;
		$this->mDirname = $dirname;
		return true;
	}

	public function getDirname() {
		return $this->mDirname;
	}

	public function &create() {}

	public function &get($label_id) {
		$obj = new Cosmoapi_DataObject($this->mDirname);
		$sql = sprintf('SELECT * FROM `%s` WHERE `label_id`=%u', $this->db->prefix($this->mDirname . '_master'), $label_id);
		if (($result = $this->db->query($sql)) === false) {
			$obj = null;
			return $obj;
		}
		$row = $this->db->fetchArray($result);
		if (!$obj->setArray($row)) {
			$obj = null;
			return $obj;
		}
		return $obj;
	}

	public function getIds() {
		$sql = sprintf('SELECT `label_id`,`label` FROM `%s`', $this->db->prefix($this->mDirname . '_master'));
		if (($result = $this->db->query($sql)) === false)
			return $ret;
		while ($row = $this->db->fetchArray($result)) {
			$ret[$row['label_id']] = $row['label'];
		}
		return $ret;
	}

	public function searchByKeyword($keyword) {
		$kwHandler =& Cosmoapi_Utils::getTrustModuleHandler('keyword', COSMOAPI_TRUST_DIRNAME);
		$kwHandler->setDirname($this->mDirname);
		$kwIds = $kwHandler->search($keyword);
		$ret = array();
		if (empty($kwIds))
			return $ret;
		$kwArr = array();
		foreach ($kwIds as $kw_id) {
			$kwArr[] = '['. $kw_id .']';
		}
		$sql = sprintf('SELECT `label_id`,`label` FROM `%s` WHERE `keyword` LIKE \'%%%s%%\'', $this->db->prefix($this->mDirname . '_master'), implode(',', $kwArr));
		if (($result = $this->db->query($sql)) === false)
			return $ret;
		while ($row = $this->db->fetchArray($result)) {
			$ret[$row['label_id']] = $row['label'];
		}
		return $ret;
	}

	public function getItemFilePath($item_id) {
		$itemHandler =& Cosmoapi_Utils::getTrustModuleHandler('item', COSMOAPI_TRUST_DIRNAME);
		$itemHandler->setDirname($this->mDirname);
		$itemObj = $itemHandler->get($item_id);
		if (!$itemObj || $itemObj->mType != 'file')
			return false;
		$path = sprintf('%s/modules/%s/extract/%u/data/%s', XOOPS_ROOT_PATH, $this->mDirname, $itemObj->mLabelId, (!empty($itemObj->mPath) ? $itemObj->mPath . '/' : '') . $itemObj->mName);
		if (!file_exists($path))
			return false;
		return $path;
	}

	public function update($dataId, $key, $value) {
		$compHandler =& Cosmoapi_Utils::getTrustModuleHandler('component', COSMOAPI_TRUST_DIRNAME);
		$compHandler->setDirname($this->mDirname);
		return $compHandler->update($dataId, $key, $value);
	}

	public function insert(&$dataObj) {}
	public function delete(&$dataObj) {}
}
