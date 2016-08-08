<?php

if (!isset($mydirname)) {
    exit();
}

$constpref = '_MD_'.strtoupper($mydirname);

if (defined($constpref.'_LOADED')) {
    return;
}

// system
define($constpref.'_LOADED', 1);

define($constpref.'_ERROR_REQUIRED', '{0} is required.');
define($constpref.'_ERROR_MINLENGTH', 'Input {0} with {1} or more characters.');
define($constpref.'_ERROR_MAXLENGTH', 'Input {0} with {1} or less characters.');
define($constpref.'_ERROR_EXTENSION', 'Uploaded file\'s extension does not match any entry in the allowed list.');
define($constpref.'_ERROR_INTRANGE', 'Incorrect input on {0}.');
define($constpref.'_ERROR_MIN', 'Input {0} with {1} or more numeric value.');
define($constpref.'_ERROR_MAX', 'Input {0} with {1} or less numeric value.');
define($constpref.'_ERROR_OBJECTEXIST', 'Incorrect input on {0}.');
define($constpref.'_ERROR_DBUPDATE_FAILED', 'Failed updating database.');
define($constpref.'_ERROR_EMAIL', '{0} is an incorrect email address.');
define($constpref.'_ERROR_CONTENT_IS_NOT_FOUND', 'Content is not found');
define($constpref.'_ERROR_NO_PERMISSION', 'You don\'t have permission');
