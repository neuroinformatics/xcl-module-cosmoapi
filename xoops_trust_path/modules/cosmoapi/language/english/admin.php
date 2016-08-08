<?php

if (!isset($mydirname)) {
    exit();
}

$constpref = '_AD_'.strtoupper($mydirname);

if (defined($constpref.'_LOADED')) {
    return;
}

// system
define($constpref.'_LOADED', 1);
