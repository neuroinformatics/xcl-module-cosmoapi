<?php

class Cosmoapi_ComponentObject
{
    public $mCompId;
    public $mLabelId;
    public $mValue;
    public $mName;

    public function __construct($row, $name)
    {
        if ($row === false) {
            return false;
        }
        $this->mCompId = $row['comp_id'];
        $this->mLableId = $row['label_id'];
        $this->mValue = $row['value'];
        $this->mName = $name;

        return true;
    }
}

class Cosmoapi_ComponentHandler
{
    private $db;
    private $mDirname;
    private $mComponentMaster = array();

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

    public function getListByLabelId($label_id)
    {
        $sql = sprintf('SELECT * FROM `%s` WHERE `label_id`=%u', $this->db->prefix($this->mDirname.'_component'), $label_id);
        if (($result = $this->db->query($sql)) === false) {
            return false;
        }
        $ret = array();
        while ($row = $this->db->fetchArray($result)) {
            $comp_id = $row['comp_id'];
            $name = isset($this->mComponentMaster[$comp_id]) ? $this->mComponentMaster[$comp_id]['name'] : '';
            $ret[$comp_id] = new Cosmoapi_ComponentObject($row, $name);
        }

        return $ret;
    }

    public function update($label_id, $key, $value)
    {
        $comp_id = false;
        foreach ($this->mComponentMaster as $master) {
            if ($master['name'] == $key) {
                $comp_id = $master['comp_id'];
                break;
            }
        }
        if ($comp_id === false) {
            return false;
        }
        $sql = sprintf('REPLACE INTO `%s` (`comp_id`, `label_id`, `value`) VALUES (%u, %u, %s)', $this->db->prefix($this->mDirname.'_component'), $comp_id, $label_id, $this->db->quoteString($value));
        if (($result = $this->db->query($sql)) === false) {
            return false;
        }

        return true;
    }

    protected function _load()
    {
        $sql = sprintf('SELECT * FROM `%s`', $this->db->prefix($this->mDirname.'_component_master'));
        if (($result = $this->db->query($sql)) === false) {
            return false;
        }
        while ($row = $this->db->fetchArray($result)) {
            $comp_id = $row['comp_id'];
            $this->mComponentMaster[$comp_id] = $row;
        }

        return true;
    }
}
