<?php

$xoopsOption['nocommon'] = true ;
require_once '../../mainfile.php';

$mydirname = basename(dirname(__FILE__));
require dirname(__FILE__) . '/mytrustdirname.php';
require_once XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/module_icon.php';

