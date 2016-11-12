<?php

class Cosmoapi_LinkObject
{
    public $mLinkId;
    public $mLabelId;
    public $mType;
    public $mUid;
    public $mName;
    public $mHref;
    public $mNote;
    public $mTypeStr;

    public function __construct($row)
    {
        if ($row === false) {
            return false;
        }
        $this->mLinkId = $row['link_id'];
        $this->mLabelId = $row['label_d'];
        $this->mType = $row['type'];
        $this->mUid = $row['uid'];
        $this->mName = $row['name'];
        $this->mHref = $row['href'];
        $this->mNote = $row['note'];
        $this->mTypeStr = $row['type'] == 1 ? 'internal' : 'external';

        return true;
    }
}

class Cosmoapi_LinkHandler
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

    public function getLinksByLabelId($label_id)
    {
        $sql = sprintf('SELECT * FROM `%s` WHERE `label_id`=%u', $this->db->prefix($this->mDirname.'_link'), $label_id);
        if (($result = $this->db->query($sql)) === false) {
            return false;
        }
        $ret = array();
        while ($row = $this->db->fetchArray($result)) {
            $obj = new Cosmoapi_LinkObject($row);
            $ret[] = $obj;
        }
        $this->db->freeRecordSet($result);

        return $ret;
    }
}
