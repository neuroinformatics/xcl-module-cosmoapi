<?php

class Cosmoapi_KeywordObject
{
    public $mDirname;
    public $mKeywordId;
    public $mKeyword;
    public $mSort;
    public $mPath;

    public function __construct($row)
    {
        if ($row === false) {
            return false;
        }
        $this->mKeywordId = $row['kw_id'];
        $this->mKeyword = $row['keyword'];
        $this->mSort = $row['sort'];
        $this->mPath = explode('/', substr($row['path'], 1, -1));

        return true;
    }
}

class Cosmoapi_KeywordHandler
{
    private $db;
    private $mDirname;
    private $mKeywords = array();

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
        if (!$this->_load()) {
            return false;
        }

        return true;
    }

    public function getDirname()
    {
        return $this->mDirname;
    }

    public function get($kw_id)
    {
        return $this->mKeywords[$kw_id];
    }

    public function search($keyword)
    {
        $ret = array();
        foreach ($this->mKeywords as $obj) {
            if ($obj->mKeyword == $keyword) {
                $ret[] = $obj->mKeywordId;
            }
        }

        return $ret;
    }

    protected function _load()
    {
        $sql = sprintf('SELECT * FROM `%s` ORDER BY `kw_id` ASC', $this->db->prefix($this->mDirname.'_keyword'));
        if (($result = $this->db->query($sql)) === false) {
            return false;
        }
        while ($row = $this->db->fetchArray($result)) {
            $kw_id = $row['kw_id'];
            $this->mKeywords[$kw_id] = new Cosmoapi_KeywordObject($row);
        }

        return true;
    }
}
