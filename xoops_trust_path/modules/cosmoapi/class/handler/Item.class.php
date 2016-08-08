<?php

class Cosmoapi_ItemObject
{
    public $mItemId;
    public $mLabelId;
    public $mType;
    public $mName;
    public $mPath;
    public $mRegDate;
    public $mRegUserId;

    public function __construct($row)
    {
        if ($row === false) {
            return false;
        }
        $this->mItemId = $row['item_id'];
        $this->mLabelId = $row['label_id'];
        $this->mType = $row['type'];
        $this->mName = $row['name'];
        $this->mPath = $row['path'];
        $this->mRegDate = intval($row['reg_date']);
        $this->mRegUserId = intval($row['reg_user']);

        return true;
    }
}

class Cosmoapi_ItemHandler
{
    private $db;
    private $mDirname;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function setDirname($dirname)
    {
        if (!preg_match('/^newdb.*$/', $dirname)) {
            return false;
        }
        $moduleHandler = &xoops_gethandler('module');
        $moduleObj = &$moduleHandler->getByDirname($dirname);
        if (!$moduleObj) {
            return false;
        }
        $this->mDirname = $dirname;

        return true;
    }

    public function getDirname()
    {
        return $this->mDirname;
    }

    public function get($item_id)
    {
        $sql = sprintf('SELECT * FROM `%s` WHERE `item_id`=%u', $this->db->prefix($this->mDirname.'_item'), $item_id);
        if (($result = $this->db->query($sql)) === false) {
            return false;
        }
        $row = $this->db->fetchArray($result);
        if (!$row) {
            return false;
        }

        return new Cosmoapi_ItemObject($row);
    }

    public function getListByLabelId($label_id)
    {
        $sql = sprintf('SELECT * FROM `%s` WHERE `label_id`=%u', $this->db->prefix($this->mDirname.'_item'), $label_id);
        if (($result = $this->db->query($sql)) === false) {
            return false;
        }
        $ret = array();
        while ($row = $this->db->fetchArray($result)) {
            $item_id = $row['item_id'];
            $ret[$item_id] = new Cosmoapi_ItemObject($row);
        }

        return $ret;
    }
}
