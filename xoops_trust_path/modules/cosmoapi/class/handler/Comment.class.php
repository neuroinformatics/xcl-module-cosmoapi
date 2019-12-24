<?php

class Cosmoapi_CommentObject
{
    public $mComId;
    public $mPcomId;
    public $mSubject;
    public $mMessage;
    public $mRegDate;
    public $mRegUser;

    public function __construct($row)
    {
        if ($row === false) {
            return false;
        }
        $this->mComId = $row['com_id'];
        $this->mPcomId = $row['pcom_id'];
        $this->mSubject = $row['subject'];
        $this->mMessage = $row['message'];
        $this->mRegDate = $row['reg_date'];
        $this->mRegUser = $row['reg_user'];

        return true;
    }
}

class Cosmoapi_CommentTopicObject
{
    public $mTopicId;
    public $mLabelId;
    public $mComId;
    public $mType;
    public $mComment;
    public $mReplies;

    public function __construct($row)
    {
        if ($row === false) {
            return false;
        }
        $this->mTopicId = $row['topic_id'];
        $this->mLabelId = $row['label_id'];
        $this->mComId = $row['com_id'];
        $this->mType = $row['type'];

        return true;
    }
}

class Cosmoapi_CommentHandler
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

    public function getCommentsByLabelId($label_id)
    {
        $sql = sprintf('SELECT * FROM `%s` WHERE `label_id`=%u', $this->db->prefix($this->mDirname.'_comment_topic'), $label_id);
        if (($result = $this->db->query($sql)) === false) {
            return false;
        }
        $ret = array();
        while ($row = $this->db->fetchArray($result)) {
            $topic_id = $row['topic_id'];
            $com_id = $row['com_id'];
            $obj = new Cosmoapi_CommentTopicObject($row);
            $obj->mComment = $this->_getComment($com_id);
            $obj->mReplies = $this->_getCommentReplies($com_id);
            $ret[$topic_id] = $obj;
        }
        $this->db->freeRecordSet($result);

        return $ret;
    }

    private function _getComment($com_id)
    {
        $sql = sprintf('SELECT * FROM `%s` WHERE `com_id`=%u', $this->db->prefix($this->mDirname.'_comment'), $com_id);
        if (($result = $this->db->query($sql)) === false) {
            return false;
        }
        $row = $this->db->fetchArray($result);
        if ($row === false) {
            return false;
        }
        $ret = new Cosmoapi_CommentObject($row);
        $this->db->freeRecordSet($result);

        return $ret;
    }

    private function _getCommentReplies($topic_id)
    {
        $sql = sprintf('SELECT * FROM `%s` WHERE `pcom_id`=%u', $this->db->prefix($this->mDirname.'_comment'), $topic_id);
        if (($result = $this->db->query($sql)) === false) {
            return false;
        }
        $ret = array();
        while ($row = $this->db->fetchArray($result)) {
            $com_id = $row['com_id'];
            $ret[$com_id] = new Cosmoapi_CommentObject($row);
        }
        $this->db->freeRecordSet($result);

        return $ret;
    }
}
