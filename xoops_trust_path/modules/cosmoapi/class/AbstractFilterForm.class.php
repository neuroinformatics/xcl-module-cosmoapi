<?php

/**
 * abstract filter form.
 */
abstract class Cosmoapi_AbstractFilterForm
{
    /**
     * sort values.
     *
     * @var Enum[]
     */
    public $mSort = array();

    /**
     * sort keys.
     *
     * @var string[]
     */
    public $mSortKeys = array();

    /**
     * page navigator.
     *
     * @var XCube_PageNavigator
     */
    public $mNavi = null;

    /**
     * object handler.
     *
     * @var XoopsObjectGenericHandler
     */
    protected $_mHandler = null;

    /**
     * search criteria.
     *
     * @var CriteriaElement
     */
    protected $_mCriteria = null;

    /**
     * get id.
     * 
     * @return int
     */
    protected function _getId()
    {
    }

    /**
     * get object handler.
     * 
     * @return XoopsObjectGenericHandler
     */
    protected function &_getHandler()
    {
    }

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->_mCriteria = new CriteriaCompo();
    }

    /**
     * prepare.
     * 
     * @param XCube_PageNavigator       &$navi
     * @param XoopsObjectGenericHandler &$handler
     */
    public function prepare(&$navi, &$handler)
    {
        $this->mNavi = &$navi;
        $this->_mHandler = &$handler;
        $this->mNavi->mGetTotalItems->add(array(&$this, 'getTotalItems'));
    }

    /**
     * get total items.
     * 
     * @param int &$total
     */
    public function getTotalItems(&$total)
    {
        $total = $this->_mHandler->getCount($this->getCriteria());
    }

    /**
     * fetch sort condition.
     */
    protected function fetchSort()
    {
        $root = &XCube_Root::getSingleton();
        $sortReq = $root->mContext->mRequest->getRequest($this->mNavi->mPrefix.'sort');
        $sortArr = (is_array($sortReq)) ? $sortReq : array($sortReq);
        foreach ($sortArr as $sort) {
            if (!is_null($sort)) {
                $this->mSort[] = intval($sort);
            }
        }

        if (count($this->mSort) == 0) {
            if (is_array($this->getDefaultSortKey())) {
                $this->mSort = $this->getDefaultSortKey();
            } else {
                $this->mSort[] = $this->getDefaultSortKey();
            }
        }

        foreach (array_keys($this->mSort) as $k) {
            $this->mNavi->mSort[$this->mNavi->mPrefix.'sort'.$k] = $this->mSort[$k];
        }
    }

    /**
     * fetch.
     */
    public function fetch()
    {
        $this->mNavi->fetch();
        $this->fetchSort();
    }

    /**
     * get sort value.
     * 
     * @param int $k
     *
     * @return Enum
     */
    public function getSort($k)
    {
        $sortkey = abs($this->mSort[$k]);

        return $this->mSortKeys[$sortkey];
    }

    /**
     * get order.
     * 
     * @param int $k
     * 
     * @return Enum
     */
    public function getOrder($k)
    {
        return ($this->mSort[$k] < 0) ? 'desc' : 'asc';
    }

    /**
     * get criteria.
     * 
     * @param int $start
     * @param int $limit
     *
     * @return CriteriaElement
     */
    public function &getCriteria($start = null, $limit = null)
    {
        $t_start = ($start === null) ? $this->mNavi->getStart() : intval($start);
        $t_limit = ($limit === null) ? $this->mNavi->getPerpage() : intval($limit);
        $criteria = $this->_mCriteria;
        $criteria->setStart($t_start);
        $criteria->setLimit($t_limit);

        return $criteria;
    }
}
