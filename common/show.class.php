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
 * @copyright   2015 Jonathan Coulet (j.coulet@gmail.com)  
 * @copyright   2013-2014 Florent Fortat (florent.fortat@maxgun.fr) / Jonathan Coulet (j.coulet@gmail.com) / Frédéric Kaplon (frederic.kaplon@me.com)
 * @copyright   2010-2012 Florent Fortat (florent.fortat@maxgun.fr) / Jonathan Coulet (j.coulet@gmail.com)
 * @copyright   2010 Jonathan Coulet (j.coulet@gmail.com)  
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

defined('ROOT') OR exit('No direct script access allowed');

class show{
    
     // affiche un message d'alerte (admin + theme)
     public static function showMsg($msg, $type){
      $core = core::getInstance();
      if(ROOT == './'){
     	$class = array(
     		'error'   => 'error',
     		'success' => 'success',
     		'info'    => 'info',
     		'warning' => 'warning',
     	);

          if (!isset($class[$type])) {
               $type = 'info';
          }
     	$data = '';
     	eval($core->callHook('startShowMsg'));
     	if($msg != '') $data = '<div id="msg" class="'.$class[$type].'"><p>'.nl2br($msg).'</p></div>';
      }
      else{
	 $class = array(
     		'error'   => 'alert',
     		'success' => 'success',
     		'info'    => 'info',
     		'warning' => 'warning',
     	);

          if (!isset($class[$type])) {
               $type = 'info';
          }

     	$data = '';
     	eval($core->callHook('startShowMsg'));
     	if($msg != '') $data = '<div data-alert class="alert-box '.$class[$type].' radius">
     	                                <p>'.nl2br($msg).'</p><a href="#" class="close">&times;</a>
     	                        </div>';
      }
     	eval($core->callHook('endShowMsg'));
     	echo $data;
     }

     // affiche les balises "link" type css (admin + theme)
     public static function showLinkTags($format = '<link href="[file]" rel="stylesheet" type="text/css" />'){
      $core = core::getInstance();
     	global $pluginsManager;
     	$data = '';
     	eval($core->callHook('startShowLinkTags'));
     	foreach($pluginsManager->getPlugins() as $k=>$plugin) if($plugin->getConfigval('activate') == 1){
     		if(ROOT == './' && $plugin->getConfigVal('activate') && $plugin->getPublicCssFile()) $data.= str_replace('[file]', $plugin->getPublicCssFile(), $format);
     		elseif(ROOT == '../' && $plugin->getConfigVal('activate') && $plugin->getAdminCssFile()) $data.= str_replace('[file]', $plugin->getAdminCssFile(), $format);
     	}
     	if(ROOT == './') $data.= str_replace('[file]', $core->getConfigVal('siteUrl').'/'.'theme/'.$core->getConfigVal('theme').'/styles.css', $format);
     	eval($core->callHook('endShowLinkTags'));
     	echo $data;
     }

     // affiche les balises "script" type javascript (admin + theme)
     public static function showScriptTags($format = '<script type="text/javascript" src="[file]"></script>') {
      $core = core::getInstance();
     	global $pluginsManager;
     	$data = '';
     	eval($core->callHook('startShowScriptTags'));
     	foreach($pluginsManager->getPlugins() as $k=>$plugin) if($plugin->getConfigval('activate') == 1){
     		if(ROOT == './' && $plugin->getConfigVal('activate') && $plugin->getPublicJsFile()) $data.= str_replace('[file]', $plugin->getPublicJsFile(), $format);
     		elseif(ROOT == '../' && $plugin->getConfigVal('activate') && $plugin->getAdminJsFile()) $data.= str_replace('[file]', $plugin->getAdminJsFile(), $format);
     	}
     	if(ROOT == './') $data.= str_replace('[file]', $core->getConfigVal('siteUrl').'/'.'theme/'.$core->getConfigVal('theme').'/scripts.js', $format);
     	eval($core->callHook('endShowScriptTags'));
     	echo $data;
     }

     // affiche une balise textarea (admin)
     public static function showAdminEditor($name, $content, $id='editor', $class='editor') {
      $core = core::getInstance();
     	eval($core->callHook('startShowAdminEditor'));
     	$data = '<textarea name="'.$name.'" id="'.$id.'" class="'.$class.'">'.$content.'</textarea>';
     	eval($core->callHook('endShowAdminEditor'));
     	echo $data;
     }

     // affiche un input hidden contenant le token (admin)
     public static function showAdminTokenField() {
      $core = core::getInstance();
     	eval($core->callHook('startShowAdminTokenField'));
     	$output = '<input type="hidden" name="token" value="'.administrator::getToken().'" />';
     	eval($core->callHook('endShowAdminTokenField'));
     	echo $output;
     }
   
     // affiche le contenu de la meta title (theme)
     public static function showTitleTag() {
      $core = core::getInstance();
     	global $runPlugin;
     	eval($core->callHook('startShowtitleTag'));
     	$data = $runPlugin->getTitleTag();
     	eval($core->callHook('endShowtitleTag'));
     	echo $data;
     }

     // affiche le contenu de la meta description (theme)
     public static function showMetaDescriptionTag() {
      $core = core::getInstance();
     	global $runPlugin;
     	eval($core->callHook('startShowMetaDescriptionTag'));
     	$data = $runPlugin->getMetaDescriptionTag();
     	eval($core->callHook('endShowMetaDescriptionTag'));
     	echo $data;
     }

     // affiche le titre de page (theme)
     public static function showMainTitle($format = '<h1>[mainTitle]</h1>') {
      $core = core::getInstance();
     	global $runPlugin;
     	eval($core->callHook('startShowMainTitle'));
     	if($core->getConfigVal('hideTitles') == 0 && $runPlugin->getMainTitle() != ''){
     		$data = $format;
     		$data = str_replace('[mainTitle]', $runPlugin->getMainTitle(), $data);
     	}
     	else $data = '';
     	eval($core->callHook('endShowMainTitle'));
     	echo $data;
     }

     // affiche le nom du site (theme)
     public static function showSiteName() {
      $core = core::getInstance();
     	eval($core->callHook('startShowSiteName'));
     	$data = $core->getConfigVal('siteName');
     	eval($core->callHook('endShowSiteName'));
     	echo $data;
     }

     // affiche la escription du site (theme)
     public static function showSiteDescription() {
      $core = core::getInstance();
     	eval($core->callHook('startShowSiteDescription'));
     	$data = $core->getConfigVal('siteDescription');
     	eval($core->callHook('endShowSiteDescription'));
     	echo $data;
     }

     // affiche l'url du site (theme)
     public static function showSiteUrl() {
      $core = core::getInstance();
     	eval($core->callHook('startShowSiteUrl'));
     	$data = $core->getConfigVal('siteUrl');
     	eval($core->callHook('endShowSiteUrl'));
     	echo $data;
     }

     // affiche la langue courante (theme)
     public static function showSiteLang() {
      $core = core::getInstance();
     	eval($core->callHook('startShowSiteLang'));
     	$data = $core->getConfigVal('siteLang');
     	eval($core->callHook('endShowSiteLang'));
     	echo $data;
     }

     // affiche le temps d'execution (theme)
     public static function showExecTime() {
     	global $time;
	$core = core::getInstance();
     	eval($core->callHook('startShowExecTime'));
     	$data = round(microtime(true) - $time, 3);
     	eval($core->callHook('endShowExecTime'));
     	echo $data;
     }

     // affiche la navigation principale (theme)
     public static function showMainNavigation($format = '<li><a href="[target]" target="[targetAttribut]">[label]</a></li>') {
     	global $pluginsManager;
	$core = core::getInstance();
     	$data = '';
     	eval($core->callHook('startShowMainNavigation'));
     	foreach($pluginsManager->getPlugins() as $k=>$plugin) if($plugin->getConfigval('activate') == 1){
     		foreach($plugin->getNavigation() as $k2=>$item){
     			$temp = $format;
     			$temp = str_replace('[target]', $item['target'], $temp);
     			$temp = str_replace('[label]', $item['label'], $temp);
     			$temp = str_replace('[targetAttribut]', $item['targetAttribut'], $temp);
     			$data.= $temp;
     		}
     	}
     	eval($core->callHook('endShowMainNavigation'));
     	echo $data;
     }

     // affiche le theme courant (theme)
     public static function showTheme($format = '<a onclick="window.open(this.href);return false;" href="[authorWebsite]">[name]</a>') {
     	//global $themes;
	$core = core::getInstance();
     	eval($core->callHook('startShowTheme'));
     	$data = $format;
     	$data = str_replace('[authorWebsite]', $core->getThemeInfo('authorWebsite'), $data);
     	$data = str_replace('[author]', $core->getThemeInfo('author'), $data);
     	$data = str_replace('[name]', $core->getThemeInfo('name'), $data);
	$data = str_replace('[id]', $core->getConfigVal('theme'), $data);
     	eval($core->callHook('endShowTheme'));
     	echo $data;
     }

     // affiche l'identifiant du plugin courant (theme)
     public static function showPluginId(){
      $core = core::getInstance();
     	global $runPlugin;
     	eval($core->callHook('startShowPluginId'));
     	$data = $runPlugin->getName();
     	eval($core->callHook('endShowPluginId'));
     	echo $data;
     }
    
}
?>