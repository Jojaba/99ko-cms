<?php
defined('ROOT') OR exit('No direct script access allowed');
# Cr�ation, de la page
$id = ($core->getUrlParam(1)) ? $core->getUrlParam(1) : false;
if(!$id) $pageItem = $page->createHomepage();
elseif($pageItem = $page->create($id)){}
else error404();
# Gestion du titre
if($runPlugin->getConfigVal('hideTitles')) $runPlugin->setMainTitle('');
else $runPlugin->setMainTitle(($pageItem->getMainTitle() != '') ? $pageItem->getMainTitle() : $pageItem->getName());
# Gestion des metas
if($pageItem->getIsHomepage()){
    if($pageItem->getMetaTitleTag() == '') $runPlugin->setTitleTag($pageItem->getName());
    else $runPlugin->setTitleTag($pageItem->getMetaTitleTag());
    if($pageItem->getMetaDescriptionTag() == '') $runPlugin->setMetaDescriptionTag($core->getConfigVal('siteDescription'));
    else $runPlugin->setMetaDescriptionTag($pageItem->getMetaDescriptionTag());
}
else{
    if($pageItem->getMetaTitleTag() == '') $runPlugin->setTitleTag($pageItem->getName());
    else $runPlugin->setTitleTag($pageItem->getMetaTitleTag());
    $runPlugin->setMetaDescriptionTag($pageItem->getMetaDescriptionTag());
}
// template
$pageFile = ($pageItem->getFile()) ? THEMES .$core->getConfigVal('theme').'/'.$pageItem->getFile() : false;
?>