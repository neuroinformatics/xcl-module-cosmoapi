<?php

require_once dirname(__FILE__).'/class/ModuleIcon.class.php';

$fname = dirname(__FILE__).'/images/module_icon.png';
$func = array(ucfirst($mytrustdirname).'_ModuleIcon', 'render');

call_user_func_array($func, array($fname, $mydirname, $mytrustdirname));
