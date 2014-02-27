<?php
/**
 * 99ko cms
 *
 * This source file is part of the 99ko cms. More information,
 * documentation and support can be found at http://99ko.hellojo.fr
 *
 * @package     99ko
 *
 * @author      Jonathan Coulet (j.coulet@gmail.com)
 * @copyright   2013-2014 Florent Fortat (florent.fortat@maxgun.fr) / Jonathan Coulet (j.coulet@gmail.com) / Frédéric Kaplon (frederic.kaplon@me.com)
 * @copyright   2010-2012 Florent Fortat (florent.fortat@maxgun.fr) / Jonathan Coulet (j.coulet@gmail.com)
 * @copyright   2010 Jonathan Coulet (j.coulet@gmail.com)  
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
defined('ROOT') OR exit('No direct script access allowed');

include_once('util.lib.php');
include_once('plugin.class.php');
include_once('show.lib.php');
 /**
  * Fonctions internes
  */

/**
 * Renvoie la configuration complète du core ou une valeur précise
 * @return : array
 */
function getCoreConf($k = ''){
	global $coreConf;
	$data = ($coreConf) ? $coreConf : utilReadJsonFile(DATA. 'config.txt', true);
	//$data = ($coreConf) ? $coreConf : json_decode(@file_get_contents(DATA. 'config.txt'), true);
	if($k != '') return $data[$k];
	else return $data;
}

/**
 * Enregistre la configuration du core
 * @param : $val (valeur a updater), $append (tableau de nouvelles valeurs)
 */
function saveConfig($val, $append = array()){
	$config = utilReadJsonFile(DATA. 'config.txt', true);   
	//json_decode(@file_get_contents(DATA. 'config.txt'), true);
	$config = array_merge($config, $append);
	foreach($config as $k=>$v) if(isset($val[$k])) $config[$k] = $val[$k];
	return utilWriteJsonFile(DATA. 'config.txt', $config);
	//if(@file_put_contents(DATA. 'config.txt', json_encode($config), 0666)) return true;
	//return false;
}

/**
 * Appelle un hook
 * @param : $hook
 * @return : string (PHP)
 */
function callHook($hookName){
	global $hooks;
	$return = '';
	if(isset($hooks[$hookName])) foreach($hooks[$hookName] as $function){
		$return.= call_user_func($function);
	}
	return $return;
}

/**
 * Ajoute un hook
 * @param : $hookName (nom du hook), $function (fonction a executer)
 */
function addHook($hookName, $function){
	global $hooks;
	$hooks[$hookName][] = $function;
}

/**
 * liste le dossier theme
 * @return : array
 */
function listThemes(){
	$data = array();
	$items = utilScanDir(THEMES);
	foreach($items['dir'] as $file){
		$data[$file] = getThemeInfos($file);
		$screenshot = THEMES .$file. '/screenshot.jpg';
		if(!file_exists($screenshot)) $screenshot = '';	
		$data[$file]['screenshot'] = $screenshot;
	}
	return $data;
}

/**
 * Détecte l'url de base
 * @return : string (URL de base)
 */
function getSiteUrl(){
        $siteUrl = str_replace(array('install.php', '/admin/index.php'), array('', ''), $_SERVER['SCRIPT_NAME']);
        $isSecure = false;
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') $isSecure = true;
        elseif(!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') $isSecure = true;
        $REQUEST_PROTOCOL = $isSecure ? 'https' : 'http';
        $siteUrl = $REQUEST_PROTOCOL.'://'.$_SERVER['HTTP_HOST'].$siteUrl;
        $pos = mb_strlen($siteUrl)-1;
        if($siteUrl[$pos] == '/') $siteUrl = substr($siteUrl, 0, -1);
        return $siteUrl;
}

/**
 * Renvois les infos d'un thème
 * @param : string (nom du thème)
 * @return : array
 */
function getThemeInfos($name){
    $data = utilReadJsonFile(THEMES .$name. '/infos.json', true);
	//$data = json_decode(@file_get_contents(THEMES .$name. '/infos.json'), true);
	return $data;
}

/**
 * Génère une URL réécrite ou standard
 * @param : $plugin (id plugin), $params (tableau de paramètres)
 * @return : URL (string)
 */
function rewriteUrl($plugin, $params = array()){
	if(getCoreConf('urlRewriting')){
		$url = $plugin.'/';
		if(count($params) > 0){
			foreach($params as $k=>$v) $url.= utilStrToUrl($v).',';
			$url = trim($url, ',');
			$url.= '.html';
		}
	}
	else{
		$url = 'index.php?p='.$plugin;
		foreach($params as $k=>$v) $url.= '&amp;'.$k.'='.utilStrToUrl($v);
	}
	return $url;
}

/**
 * Retourne les paramètres de l'URL dans un array
 * @param : string (nom du thème)
 * @return : array
 */
function getUrlParams(){
	$data = array();
	if(getCoreConf('urlRewriting')){
		if(isset($_GET['param'])) $data = explode(',', $_GET['param']);
	}
	else{
		foreach($_GET as $k=>$v) if($k != 'p') $data[] = $v;
	}
	return $data;
}

/**
 * hash
 */
function encrypt($data){
	return hash_hmac('sha1', $data, KEY);
}

/**
 * liste le dossier lang
 * @return : array
 */
function listLangs(){
	$data = array('en');
	$items = utilScanDir(LANG);
	foreach($items['file'] as $k=>$v) $data[] = substr($v, 0, 2);
	return $data;
}

/**
 * Formate les phrases
 */
function lang($k){
	global $lang;
	if(getCoreConf('siteLang') == 'en') return $k;
	#elseif(array_key_exists($k, $lang)) return $lang[$k];
	elseif (is_array($lang) && array_key_exists($k, $lang)) return $lang[$k];
	else return $k;
}

/**
 * renvoie la page d'erreur 404
 */
function error404(){
	header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	$url = getCoreConf('siteUrl');
	$lang = getCoreConf('siteLang');
	$msg = lang("The requested page does not exist.", "core");
	$back = lang("Back to website", "core");
	echo '<!DOCTYPE html>
	<html lang="'.$lang.'">
	<head>
	<meta charset="utf-8" />
	<title>404</title>
	</head>
	<body>
	<p>'.$msg.'<br /><< <a href="'.$url.'">'.$back.'</a></p>
	</body>
	</html>';
	die();
}
/*
 * Vérifie la version de 99ko
 */
function newVersion($url){
	if($last = @file_get_contents($url)){
		if($last != VERSION) return $last;
	}
	return false;
}
/**
 * Affiche le GRAVATAR
 * @email - Email address to show gravatar for 
 * @size - size of gravatar 
 * @default - URL of default gravatar to use 
 * @rating - rating of Gravatar(G, PG, R, X) 
 */ 
function profil_img($email, $size, $default, $rating) {	
   $pic = explode('@',$email);
   # Récupère l'image en cache du profil.
   $profile_image = UPLOAD .$pic[0] .'.jpg';
	
   # On met en cache l'image si elle n'existe pas !
   if (!file_exists($profile_image)) {
    	 $image_url = 'https://secure.gravatar.com/avatar/' .md5(strtolower(trim($email))). '&default='.$default.'&size='.$size.'&rating='.$rating;
    	 $image = file_get_contents($image_url);
    	 file_put_contents(UPLOAD .$pic[0].'.jpg', $image);
   }	
   # Retourne l'image.
   return '<img src="' .$profile_image. '" class="th radius" width="'.$size.'" height="'.$size.'" alt="profil" />';
}
?>