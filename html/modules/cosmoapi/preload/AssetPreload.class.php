<?php

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

$mydirname = basename(dirname(dirname(__FILE__)));
require dirname(dirname(__FILE__)).'/mytrustdirname.php';
require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/preload/AssetPreload.class.php';
call_user_func_array(ucfirst($mytrustdirname).'_AssetPreloadBase::prepare', array($mydirname, $mytrustdirname));
