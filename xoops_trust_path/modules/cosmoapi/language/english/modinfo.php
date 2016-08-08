<?php

if (!isset($mydirname)) {
    exit();
}

$constpref = '_MI_'.strtoupper($mydirname);

if (defined($constpref.'_LOADED')) {
    return;
}

// system
define($constpref.'_LOADED', 1);

// install utilities
define($constpref.'_INSTALL_ERROR_MODULE_INSTALLED', 'Module not installed.');
define($constpref.'_INSTALL_ERROR_PERM_ADMIN_SET', 'Module admin permission could not set.');
define($constpref.'_INSTALL_ERROR_PERM_READ_SET', 'Module read permission could not set.');
define($constpref.'_INSTALL_MSG_MODULE_INSTALLED', 'Module "{0}" has installed.');
define($constpref.'_INSTALL_ERROR_SQL_FILE_NOT_FOUND', 'SQL file "{0}" is not found.');
define($constpref.'_INSTALL_MSG_DB_SETUP_FINISHED', 'Database setup is finished.');
define($constpref.'_INSTALL_MSG_SQL_SUCCESS', 'SQL success : {0}');
define($constpref.'_INSTALL_MSG_SQL_ERROR', 'SQL error : {0}');
define($constpref.'_INSTALL_MSG_TPL_INSTALLED', 'Template "{0}" is installed.');
define($constpref.'_INSTALL_ERROR_TPL_INSTALLED', 'Template "{0}" could not installed.');
define($constpref.'_INSTALL_ERROR_TPL_UNINSTALLED', 'Template "{0}" could not uninstalled.');
define($constpref.'_INSTALL_MSG_BLOCK_INSTALLED', 'Block "{0}" is installed.');
define($constpref.'_INSTALL_ERROR_BLOCK_COULD_NOT_LINK', 'Block "{0}" could not link to module.');
define($constpref.'_INSTALL_ERROR_PERM_COULD_NOT_SET', 'Block permission of "{0}" could not set.');
define($constpref.'_INSTALL_ERROR_BLOCK_PERM_SET', 'Block permission of "{0}" could not set.');
define($constpref.'_INSTALL_MSG_BLOCK_TPL_INSTALLED', 'Block template "{0}" is installed.');
define($constpref.'_INSTALL_ERROR_BLOCK_TPL_INSTALLED', 'Block template "{0}" could not installed.');
define($constpref.'_INSTALL_MSG_BLOCK_UNINSTALLED', 'Block "{0}" is uninstalled.');
define($constpref.'_INSTALL_ERROR_BLOCK_UNINSTALLED', 'Block "{0}" could not uninstalled.');
define($constpref.'_INSTALL_ERROR_BLOCK_PERM_DELETE', 'Block permission of "{0}" could not deleted.');
define($constpref.'_INSTALL_MSG_BLOCK_UPDATED', 'Block "{0}" is updated.');
define($constpref.'_INSTALL_ERROR_BLOCK_UPDATED', 'Block "{0}" could not updated.');
define($constpref.'_INSTALL_ERROR_BLOCK_INSTALLED', 'Block "{0}" could not installed.');
define($constpref.'_INSTALL_MSG_BLOCK_TPL_UNINSTALLED', 'Block template "{0}" is uninstalled.');
define($constpref.'_INSTALL_MSG_CONFIG_ADDED', 'Config "{0}" is added.');
define($constpref.'_INSTALL_ERROR_CONFIG_ADDED', 'Config "{0}" could not added.');
define($constpref.'_INSTALL_MSG_CONFIG_DELETED', 'Config "{0}" is deleted.');
define($constpref.'_INSTALL_ERROR_CONFIG_DELETED', 'Config "{0}" could not deleted.');
define($constpref.'_INSTALL_MSG_CONFIG_UPDATED', 'Config "{0}" is updated.');
define($constpref.'_INSTALL_ERROR_CONFIG_UPDATED', 'Config "{0}" could not updated.');
define($constpref.'_INSTALL_ERROR_CONFIG_NOT_FOUND', 'Config is not found.');
define($constpref.'_INSTALL_MSG_MODULE_INFORMATION_DELETED', 'Module information is deleted.');
define($constpref.'_INSTALL_ERROR_MODULE_INFORMATION_DELETED', 'Module information could not deleted.');
define($constpref.'_INSTALL_MSG_TABLE_DOROPPED', 'Table "{0}" is doropped.');
define($constpref.'_INSTALL_ERROR_TABLE_DOROPPED', 'Table "{0}" could not doropped.');
define($constpref.'_INSTALL_ERROR_BLOCK_TPL_DELETED', 'Block template could not deleted.<br />{0}');
define($constpref.'_INSTALL_MSG_MODULE_UNINSTALLED', 'Module "{0}" is uninstalled.');
define($constpref.'_INSTALL_ERROR_MODULOE_UNINSTALLED', 'Module "{0}" could not uninstalled.');
define($constpref.'_INSTALL_MSG_UPDATE_STARTED', 'Module update started.');
define($constpref.'_INSTALL_MSG_UPDATE_FINISHED', 'Module update is finished.');
define($constpref.'_INSTALL_ERROR_UPDATE_FINISHED', 'Module could not updated.');
define($constpref.'_INSTALL_MSG_MODULE_UPDATED', 'Module "{0}" is updated.');
define($constpref.'_INSTALL_ERROR_MODULE_UPDATED', 'Module "{0}" could not updated.');

// general information
define($constpref.'_NAME', 'CosmoAPI');
define($constpref.'_DESC', 'Provide Web API for CosmoDB');
define($constpref.'_AUTHOR', 'Neuroinformatics Japan Center, RIKEN BSI <https://nijc.brain.riken.jp/>');
define($constpref.'_CREDITS', 'Brain Atlas Hackaton 2015 Project');

// templates
define($constpref.'_TPL_LOGIN', 'Login Result XML');
define($constpref.'_TPL_DATA', 'Data XML');
define($constpref.'_TPL_SEARCH', 'Search Result XML');
define($constpref.'_TPL_UPDATE', 'Update XML');
