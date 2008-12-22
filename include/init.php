<?php

/* 
 * Zend framework init - start
 */
set_include_path(get_include_path() . PATH_SEPARATOR . "./library/");
require_once './library/Zend/Loader.php';
Zend_Loader::loadClass('Zend_Db_Table');
Zend_Loader::loadClass('Zend_Date');
Zend_Loader::loadClass('Zend_Debug');
Zend_Loader::loadClass('Zend_Auth');
Zend_Loader::loadClass('Zend_Session');
Zend_Loader::loadClass('Zend_Session_Namespace');
Zend_Loader::loadClass('Zend_Config_Ini');
Zend_Loader::loadClass('Zend_Acl');
Zend_Loader::loadClass('Zend_Acl_Role');
Zend_Loader::loadClass('Zend_Acl_Resource');
Zend_Loader::loadClass('Zend_Locale');
Zend_Loader::loadClass('Zend_Locale_Format');
Zend_Loader::loadClass('Zend_Log');
Zend_Loader::loadClass('Zend_Log_Writer_Stream');

//session_start();
Zend_Session::start();
$auth_session = new Zend_Session_Namespace('Zend_Auth');


/* 
 * Zend framework init - end
 */

$writer = new Zend_Log_Writer_Stream('./cache/si.log');
$logger = new Zend_Log($writer);



/* 
 * Smarty inint - start
 */
require_once("smarty/Smarty.class.php");

$smarty = new Smarty();

//cache directory. Have to be writeable (chmod 777)
$smarty -> compile_dir = "cache";
if(!is_writable($smarty -> compile_dir)) {
	simpleInvoicesError("cache", $smarty -> compile_dir);
	//exit("Simple Invoices Error : The folder <i>".$smarty -> compile_dir."</i> has to be writeable");
}

//adds own smarty plugins
$smarty->plugins_dir = array("plugins","include/smarty_plugins");

//add stripslash smarty function
$smarty->register_modifier("unescape","stripslashes");
/* 
 * Smarty inint - end
 */


$path = pathinfo($_SERVER['REQUEST_URI']);
//SC: Install path handling will need changes if used in non-HTML contexts
$install_path = htmlspecialchars($path['dirname']);


include_once('./config/define.php');

$config = new Zend_Config_Ini('./config/config.ini', $environment);
/*
 * Include another config file if required
 */
if($environment != 'production') {
     $config = new Zend_Config_Ini('./config/'.$environment.'.config.ini', $environment);
}

//include_once("./include/sql_patches.php");
include_once("./include/sql_queries.php");

$smarty->register_modifier("siLocal_number", array("siLocal", "number"));
$smarty->register_modifier("siLocal_number_trim", array("siLocal", "number_trim"));
include_once('./include/language.php');

include_once('./include/functions.php');

checkConnection();

include('./include/include_auth.php');
include_once('./include/manageCustomFields.php');
include_once("./include/validation.php");

//if authentication enabled then do acl check etc..
if ($config->authentication->enabled == 1 )
{
	include_once("./include/acl.php");
	include_once("./include/check_permission.php");
}
