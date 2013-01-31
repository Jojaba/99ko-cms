<?php
##########################################################################################################
# 99ko http://99ko.tuxfamily.org/
#
# Copyright (c) 2012-2013 Florent Fortat (florent.fortat@maxgun.fr) / Jonathan Coulet (j.coulet@gmail.com)
# / Frédéric Kaplon (frederic@kaplon.fr)
# Copyright (c) 2010-2012 Florent Fortat (florent.fortat@maxgun.fr) / Jonathan Coulet (j.coulet@gmail.com)
# Copyright (c) 2010 Jonathan Coulet (j.coulet@gmail.com)
##########################################################################################################

// on declare ROOT
define('ROOT', '../');
// on inclu le fichier common
include_once(ROOT.'common/common.php');
// on genere le jeton
if(!isset($_SESSION['token'])) $_SESSION['token'] = uniqid();
// on check le jeton
if(in_array(ACTION, array('save', 'del', 'saveconfig', 'saveplugins', 'login', 'logout')) && $_REQUEST['token'] != $_SESSION['token']){	
	include_once('login.php');
	die();
}
// Variables de template
$msg = '';
$data['msg'] = ''; // retro compatibilité
$version = VERSION;
$token = $_SESSION['token'];
$data['token'] = $token; // retro compatibilité
$pluginName = $runPlugin->getName();
$data['pluginName'] = $pluginName; // retro compatibilité
$navigation[-1]['label'] = 'Accueil';
$navigation[-1]['url'] = './';
$navigation[-1]['isActive'] = (!isset($_GET['p'])) ? true : false;
foreach($pluginsManager->getPlugins() as $k=>$v) if($v->getConfigVal('activate') && $v->getAdminFile()){
	$navigation[$k]['label'] = $v->getInfoVal('name');
	$navigation[$k]['url'] = 'index.php?p='.$v->getName();
	$navigation[$k]['isActive'] = (isset($_GET['p']) && $_GET['p'] == $v->getName()) ? true : false;
}
$pluginConfigTemplate = (!isset($_GET['p'])) ? false :$runPlugin->getConfigTemplate();
$pageTitle = (!isset($_GET['p'])) ? 'Bienvenue dans 99ko' : $runPlugin->getInfoVal('name');
// Actions
if(ACTION == 'login'){
	$loginAttempt = (isset($_SESSION['loginAttempt'])) ? $_SESSION['loginAttempt'] : 0;
	$loginAttempt++;
	$_SESSION['loginAttempt'] = $loginAttempt;
	if($loginAttempt > 4 || !isset($_SESSION['loginAttempt'])){
		$msg = "Veuillez attendre avant de faire une nouvelle tentative";
	}
	else{
		$pwd = $coreConf['adminPwd'];
		if(encrypt(trim($_POST['adminPwd'])) == $pwd){
			$_SESSION['admin'] = $pwd;
			$_SESSION['loginAttempt'] = 0;
			$_SESSION['token'] = uniqid();
			header('location:index.php');
			die();
		}
		else{
			$msg = "Mot de passe incorrect";
		}
	}
}
elseif(ACTION == 'logout'){
	unset($_SESSION['admin']);
	unset($_SESSION['loginAttempt']);
	unset($_SESSION['token']);
	header('location:index.php');
	die();
}
// Login mode
if(!isset($_SESSION['admin']) || $_SESSION['admin'] != $coreConf['adminPwd']){
	include_once('login.php');
}
// Homepage mode
elseif(!isset($_GET['p'])){
	if(!file_exists('../.htaccess')) $msg.= "Le fichier .htaccess est manquant !\n";
	if(file_exists('../install.php')) $msg.= "Le fichier install.php doit être supprimé !\n";
	include_once('home.php');
}
// Plugin mode
elseif(isset($_GET['p']) && $runPlugin->getAdminFile()){
	// hook
	eval(callHook('startAdminIncludePluginFile'));
	include($runPlugin->getAdminFile());
	include($runPlugin->getAdminTemplate());
	// hook
	eval(callHook('endAdminIncludePluginFile'));
}
?>