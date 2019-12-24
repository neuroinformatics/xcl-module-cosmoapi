<?php

// force load modinfo message catalog
XCube_Root::getSingleton()->mLanguageManager->loadModinfoMessageCatalog($mydirname);

$constpref = '_MI_'.strtoupper($mydirname);
if (!defined($constpref.'_LOADED')) {
    // load modinfo.php by myself. probably this case will occured only
    // if this module is not installed yet. because trust language
    // resources are supported by the language manager of legacy 2.2
    // if a module is already installed.
    $fname = dirname(__FILE__).'/language/'.XCube_Root::getSingleton()->mLanguageManager->getLanguage().'/modinfo.php';
    if (!file_exists($fname)) {
        $fname = dirname(__FILE__).'/language/'.XCube_Root::getSingleton()->mLanguageManager->getFallbackLanguage().'/modinfo.php';
    }
    require_once $fname;
}

require_once dirname(__FILE__).'/class/Utils.class.php';

//
// Define a basic manifesto.
//
$modversion['name'] = constant($constpref.'_NAME');
$modversion['version'] = 1.01;
$modversion['description'] = constant($constpref.'_DESC');
$modversion['author'] = constant($constpref.'_AUTHOR');
$modversion['credits'] = constant($constpref.'_CREDITS');
$modversion['help'] = 'help.html';
$modversion['license'] = 'GPL';
$modversion['official'] = 0;
$modversion['image'] = 'module_icon.php';
$modversion['dirname'] = $mydirname;
$modversion['trust_dirname'] = $mytrustdirname;
$modversion['role'] = 'workflow';

$modversion['cube_style'] = true;
$modversion['legacy_installer'] = array(
    'installer' => array(
        'class' => 'Installer',
        'namespace' => ucfirst($mytrustdirname),
        'filepath' => XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/admin/class/installer/Installer.class.php',
    ),
    'uninstaller' => array(
        'class' => 'Uninstaller',
        'namespace' => ucfirst($mytrustdirname),
        'filepath' => XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/admin/class/installer/Uninstaller.class.php',
    ),
    'updater' => array(
        'class' => 'Updater',
        'namespace' => ucfirst($mytrustdirname),
        'filepath' => XOOPS_TRUST_PATH.'/modules/'.$mytrustdirname.'/admin/class/installer/Updater.class.php',
    ),
);
$modversion['disable_legacy_2nd_installer'] = false;

//
// Templates
//
$modversion['templates'] = array(
    array('file' => 'login.xml', 'description' => constant($constpref.'_TPL_LOGIN')),
    array('file' => 'data.xml', 'description' => constant($constpref.'_TPL_DATA')),
    array('file' => 'search.xml', 'description' => constant($constpref.'_TPL_SEARCH')),
    array('file' => 'update.xml', 'description' => constant($constpref.'_TPL_UPDATE')),
);

//
// Admin panel setting
//
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = 'admin/index.php';
$modversion['adminmenu'] = array();

//
// Public side control setting
//
$modversion['hasMain'] = 0;
$modversion['hasSearch'] = 0;

//
// Config setting
//
$modversion['config'] = array();

//
// Block setting
//
$modversion['blocks'] = array();
