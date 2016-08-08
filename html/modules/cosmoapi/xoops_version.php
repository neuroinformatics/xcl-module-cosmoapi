<?php

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

$mydirname = basename(dirname(__FILE__));
require dirname(__FILE__).'/mytrustdirname.php';
require XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/xoops_version.php';
