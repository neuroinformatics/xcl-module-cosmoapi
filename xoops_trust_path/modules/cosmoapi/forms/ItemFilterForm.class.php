<?php

require_once dirname(dirname(__FILE__)).'/class/AbstractFilterForm.class.php';

/**
 * item filter form.
 */
class Cosmoapi_ItemFilterForm extends Cosmoapi_AbstractFilterForm
{
    const SORT_KEY_ITEM_ID = 1;
    const SORT_KEY_TITLE = 2;
    const SORT_KEY_DIRNAME = 3;
    const SORT_KEY_DATANAME = 4;
    const SORT_KEY_TARGET_ID = 5;
    const SORT_KEY_UID = 6;
    const SORT_KEY_STEP = 7;
    const SORT_KEY_STATUS = 8;
    const SORT_KEY_POSTTIME = 9;
    const SORT_KEY_DELETETIME = 10;
    const SORT_KEY_DEFAULT = self::SORT_KEY_ITEM_ID;

    /**
     * sort keys.
     *
     * @var string[]
     */
    public $mSortKeys = array(
        self::SORT_KEY_ITEM_ID => 'item_id',
        self::SORT_KEY_TITLE => 'title',
        self::SORT_KEY_DIRNAME => 'dirname',
        self::SORT_KEY_DATANAME => 'dataname',
        self::SORT_KEY_TARGET_ID => 'target_id',
        self::SORT_KEY_UID => 'uid',
        self::SORT_KEY_STEP => 'step',
        self::SORT_KEY_STATUS => 'status',
        self::SORT_KEY_POSTTIME => 'posttime',
        self::SORT_KEY_DELETETIME => 'deletetime',
    );

    /**
     * get default sort key.
     *
     * @return int[]
     */
    public function getDefaultSortKey()
    {
        return array(self::SORT_KEY_DEFAULT);
    }

    /**
     * fetch.
     */
    public function fetch()
    {
        parent::fetch();
        $root = &XCube_Root::getSingleton();
        if (($value = $root->mContext->mRequest->getRequest('item_id')) !== null) {
            $this->mNavi->addExtra('item_id', $value);
            $this->_mCriteria->add(new Criteria('item_id', $value));
        }
        if (($value = $root->mContext->mRequest->getRequest('title')) !== null) {
            $this->mNavi->addExtra('title', $value);
            $this->_mCriteria->add(new Criteria('title', $value));
        }
        if (($value = $root->mContext->mRequest->getRequest('dirname')) !== null) {
            $this->mNavi->addExtra('dirname', $value);
            $this->_mCriteria->add(new Criteria('dirname', $value));
        }
        if (($value = $root->mContext->mRequest->getRequest('dataname')) !== null) {
            $this->mNavi->addExtra('dataname', $value);
            $this->_mCriteria->add(new Criteria('dataname', $value));
        }
        if (($value = $root->mContext->mRequest->getRequest('target_id')) !== null) {
            $this->mNavi->addExtra('target_id', $value);
            $this->_mCriteria->add(new Criteria('target_id', $value));
        }
        if (($value = $root->mContext->mRequest->getRequest('uid')) !== null) {
            $this->mNavi->addExtra('uid', $value);
            $this->_mCriteria->add(new Criteria('uid', $value));
        }
        if (($value = $root->mContext->mRequest->getRequest('step')) !== null) {
            $this->mNavi->addExtra('step', $value);
            $this->_mCriteria->add(new Criteria('step', $value));
        }
        if (($value = $root->mContext->mRequest->getRequest('status')) !== null) {
            $this->mNavi->addExtra('status', $value);
            $this->_mCriteria->add(new Criteria('status', $value));
        }
        if (($value = $root->mContext->mRequest->getRequest('posttime')) !== null) {
            $this->mNavi->addExtra('posttime', $value);
            $this->_mCriteria->add(new Criteria('posttime', $value));
        }
        if (($value = $root->mContext->mRequest->getRequest('deletetime')) !== null) {
            $this->mNavi->addExtra('deletetime', $value);
            $this->_mCriteria->add(new Criteria('deletetime', $value));
        }
        foreach (array_keys($this->mSort) as $k) {
            $this->_mCriteria->addSort($this->getSort($k), $this->getOrder($k));
        }
    }
}
