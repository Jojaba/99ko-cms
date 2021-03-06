<?php
defined('ROOT') OR exit('No direct script access allowed');

/*******************************************************************************************************
** Partie obligatoire
** Les fonctions ci-dessous sont obligatoires !
** Les fonctions ci-dessous doivent être nommées de cette façon : nomdupluginConfig, nomdupluginInfos...
*******************************************************************************************************/

/*
** Exécute du code lors de l'installation
** Le code présent dans cette fonction sera exécuté lors de l'installation
** Le contenu de cette fonction est facultatif
*/
function pageInstall(){
	$page = new page();
	if(count($page->getItems()) < 1){
		$pageItem = new pageItem();
		$pageItem->setName('Accueil');
		$pageItem->setPosition(1);
		$pageItem->setIsHomepage(1);
		$pageItem->setContent('<p>L\'installation s\'est déroulée avec succès.<br />Vous pouvez maintenant vous connecter à l\'administration en utilisant le lien en bas de page et les informations de connexion qui vous ont été communiqués lors de l\'installation.</p>
<p>N\'oubliez pas de modifier vos informations de connexion et de supprimer le fichier install de votre FTP.</p>');
		$pageItem->setIsHidden(0);
		$pageItem->setFile('home.php');
		$page->save($pageItem);
		$page = new page();
		$pageItem = new pageItem();
		$pageItem->setName('Page 2');
		$pageItem->setPosition(2);
		$pageItem->setIsHomepage(0);
		$pageItem->setContent("<p>Iamque lituis cladium concrepantibus internarum non celate ut antea turbidum saeviebat ingenium a veri consideratione detortum et nullo inpositorum vel conpositorum fidem sollemniter inquirente nec discernente a societate noxiorum insontes velut exturbatum e iudiciis fas omne discessit, et causarum legitima silente defensione carnifex rapinarum sequester et obductio capitum et bonorum ubique multatio versabatur per orientales provincias, quas recensere puto nunc oportunum absque Mesopotamia digesta, cum bella Parthica dicerentur, et Aegypto, quam necessario aliud reieci ad tempus.</p><p>Haec igitur lex in amicitia sanciatur, ut neque rogemus res turpes nec faciamus rogati. Turpis enim excusatio est et minime accipienda cum in ceteris peccatis, tum si quis contra rem publicam se amici causa fecisse fateatur. Etenim eo loco, Fanni et Scaevola, locati sumus ut nos longe prospicere oporteat futuros casus rei publicae. Deflexit iam aliquantum de spatio curriculoque consuetudo maiorum.</p>");
		$pageItem->setIsHidden(0);
		$pageItem->setFile('');
		$page->save($pageItem);
		$page = new page();
		$pageItem = new pageItem();
		$pageItem->setName('Texte support');
		$pageItem->setPosition(3);
		$pageItem->setIsHomepage(0);
		$pageItem->setContent("<p>Téléchargez une version plus récente, des plugins et des thèmes sur le site officiel<br>En cas de problème avec 99ko, rendez-vous sur le forum d'entraide.</p>");
		$pageItem->setIsHidden(1);
		$pageItem->setFile('');
		$page->save($pageItem);
	}
}

/********************************************************************************************************************
** Code relatif au plugin
** La partie ci-dessous est réservé au code du plugin 
** Elle peut contenir des classes, des fonctions, hooks... ou encore du code à exécutter lors du chargement du plugin
********************************************************************************************************************/

define('PAGE_DATAPATH', DATA_PLUGIN. 'page/');
$page = new page();

foreach($page->getItems() as $k=>$pageItem) if(!$pageItem->getIsHidden()){
	$temp = (getCoreConf('defaultPlugin') == 'page' && $pageItem->getIsHomepage()) ? getCoreConf('siteUrl') : rewriteUrl('page', array('name' => $pageItem->getName(), 'id' => $pageItem->getId()));
	$pluginsManager->getPlugin('page')->addToNavigation($pageItem->getName(), $temp);
}

class page{
	private $items;
	
	public function __construct(){
		$data = array();
		if(is_dir(PAGE_DATAPATH)){
			$dataNotSorted = array();
			$items = utilScanDir(PAGE_DATAPATH, array('config.txt'));
			foreach($items['file'] as $k=>$file){
				$dataNotSorted[] = utilReadJsonFile(DATA_PLUGIN. 'page/'.$file, true);
			}
			$dataSorted = utilSort2DimArray($dataNotSorted, 'position', 'num');
			foreach($dataSorted as $pageItem){
				$data[] = new pageItem($pageItem);
			}
		}
		$this->items = $data;
	}
	
	public function getItems(){
		return $this->items;
	}
	
	public function create($id){
		foreach($this->items as $pageItem){
			if($pageItem->getId() == $id) return $pageItem;
		}
		return false;
	}
	public function createHomepage(){
		foreach($this->items as $pageItem){
			if($pageItem->getIshomepage()) return $pageItem;
		}
		return false;
	}
	public function save($obj){
		$id = intval($obj->getId());
		if($id < 1) $id = $this->makeId();
		$data = array(
			'id' => $id,
			'name' => $obj->getName(),
			'position' => $obj->getPosition(),
			'isHomepage' => $obj->getIsHomepage(),
			'content' => $obj->getContent(),
			'isHidden' => $obj->getIsHidden(),
			'file' => $obj->getFile(),
			'mainTitle' => $obj->getMainTitle(),
			'metaDescriptionTag' => $obj->getMetaDescriptionTag(),
			'metaTitleTag' => $obj->getMetaTitleTag(),
		);
		if($obj->getIsHomepage() > 0) $this->initIshomepageVal();
		if(@file_put_contents(PAGE_DATAPATH.$id.'.txt', json_encode($data), 0666)){
			$this->repairPositions($obj);
			return true;
		}
		return false;
	}
	public function del($obj){
		if($obj->getIsHomepage() < 1 && $this->count() > 1){
			if(@unlink(PAGE_DATAPATH.$obj->getId().'.txt')) return true;
		}
		return false;
	}
	public function count(){
		return count($this->items);
	}
	public function listFiles(){
		$data = array();
		$items = utilScanDir(THEMES .getCoreConf('theme').'/', array('header.php', 'footer.php', 'style.css'));
		foreach($items['file'] as $file){
			if(in_array(utilGetFileExtension($file), array('htm', 'html', 'txt', 'php'))) $data[] = $file;
		}
		return $data;
	}
	
	private function makeId(){
		$ids = array(0);
		foreach($this->items as $pageItem){
			$ids[] = $pageItem->getId();
		}
		return max($ids)+1;
	}

	private function initIshomepageVal(){
		foreach($this->items as $obj){
			$obj->setIsHomepage(0);
			$this->save($obj);
		}
	}
	
	private function repairPositions($currentObj){
		foreach($this->items as $obj) if($obj->getId() != $currentObj->getId()){
			$pos = $obj->getPosition();
			if($pos == $currentObj->getPosition()){
				$obj->setPosition($pos+1);
				$this->save($obj);
			}
		}
	}
}

class pageItem{
	private $id;
	private $name;
	private $position;
	private $isHomepage;
	private $content;
	private $isHidden;
	private $file;
	private $mainTitle;
	private $metaDescriptionTag;
	private $metaTitleTag;
	
	public function __construct($val = array()){
		if(count($val) > 0){
			$this->id = $val['id'];
			$this->name = $val['name'];
			$this->position = $val['position'];
			$this->isHomepage = $val['isHomepage'];
			$this->content = $val['content'];
			$this->isHidden = $val['isHidden'];
			$this->file = $val['file'];
			$this->mainTitle = $val['mainTitle'];
			$this->metaDescriptionTag = $val['metaDescriptionTag'];
			$this->metaTitleTag = (isset($val['metaTitleTag']) ? $val['metaTitleTag'] : '');
		}
	}

	public function setName($val){
		$val = trim($val);
		if($val == '') $val = "Page sans nom";
		$this->name = $val;
	}
	public function setPosition($val){
		$this->position = intval($val);
	}
	public function setIsHomepage($val){
		$this->isHomepage = trim($val);
	}
	public function setContent($val){
		$this->content = trim($val);
	}
	public function setIsHidden($val){
		$this->isHidden = intval($val);
	}
	public function setFile($val){
		$this->file = trim($val);
	}
	public function setMainTitle($val){
		$this->mainTitle = trim($val);
	}
	public function setMetaDescriptionTag($val){
		$val = trim($val);
		if(mb_strlen($val) > 150) $val = mb_strcut($val, 0, 150).'...';
		$this->metaDescriptionTag = $val;
	}
	public function setMetaTitleTag($val){
		$val = trim($val);
		if(mb_strlen($val) > 65) $val = mb_strcut($val, 0, 65).'...';
		$this->metaTitleTag = $val;
	}

	public function getId(){
		return $this->id;
	}
	public function getName(){
		return $this->name;
	}
	public function getPosition(){
		return $this->position;
	}
	public function getIsHomepage(){
		return $this->isHomepage;
	}
	public function getContent(){
		return $this->content;
	}
	public function getIsHidden(){
		return $this->isHidden;
	}
	public function getFile(){
		return $this->file;
	}
	public function getMainTitle(){
		return $this->mainTitle;
	}
	public function getMetaDescriptionTag(){
		return $this->metaDescriptionTag;
	}
	public function getMetaTitleTag(){
		return $this->metaTitleTag;
	}
}

function pageContent($id){
	global $page;
	if($temp = $page->create($id)){
		return $temp->getContent();
	}
}
?>
