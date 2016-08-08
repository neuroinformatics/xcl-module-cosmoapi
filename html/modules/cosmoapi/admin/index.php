<?php

require_once '../../../mainfile.php';
$mydirname = basename(dirname(dirname(__FILE__)));
require dirname(dirname(__FILE__)).'/mytrustdirname.php';
require_once XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/admin/index.php';
