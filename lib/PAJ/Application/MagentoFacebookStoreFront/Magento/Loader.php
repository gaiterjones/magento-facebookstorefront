<?php
/**
 *  
 *  Copyright (C) 2014
 *
 *
 *  @who	   	PAJ
 *  @info   	paj@gaiterjones.com
 *  @license    blog.gaiterjones.com
 * 	 
 *
 */

namespace PAJ\Application\MagentoFacebookStoreFront\Magento;

class Loader {

	protected $__;
	public $__config;
	public $__t;

	public function __construct() {
		
		$this->loadConfig();
		$this->loadTranslator();
		
	}

	// loads config
	//
	private function loadConfig()
	{
		$this->__config= new \PAJ\Application\MagentoFacebookStoreFront\config();

	}
	
	// -- connect to Magento
	protected function loadMagento($_languageCode='en')
	{
		if (file_exists($this->__config->get('pathToMagentoApp'))) {
			
			include_once($this->__config->get('pathToMagentoApp'));
			
		} else if (defined('ROOT_DIR')) {
		
			if (file_exists(ROOT_DIR . $this->__config->get('pathToMagentoApp'))) {
			
				include_once(ROOT_DIR . $this->__config->get('pathToMagentoApp'));
			
			} else if (strpos(ROOT_DIR, '.modman') !== false) {
				
				$_modmanPath=substr(ROOT_DIR, 0, strpos(ROOT_DIR, '.modman'));
								
				//throw new \Exception('modman found at '. $_modmanPath);
				
				if (file_exists($_modmanPath. '/magento/app/Mage.php')) {
					
					include_once($_modmanPath. '/magento/app/Mage.php');
					
				} else {
					throw new \Exception('modman found at '. $_modmanPath. ' could not find Magento...');
				}
			
			} else {
				throw new \Exception('Mage NOT Found at '. ROOT_DIR . $this->__config->get('pathToMagentoApp'));
			}
			
		} else {

			throw new \Exception('Mage NOT Found at '. $this->__config->get('pathToMagentoApp'));
		}
		
		
		umask(0);
		\Mage::app();
		\Mage::app()->loadArea(\Mage_Core_Model_App_Area::AREA_FRONTEND);
		
		$baseUrlMedia = \Mage::getBaseUrl(\Mage_Core_Model_Store::URL_TYPE_MEDIA);
		
		$_stores = \Mage::app()->getStores(false, true);
		
		if (isset($_stores[$_languageCode]) && $_stores[$_languageCode]->getIsActive()) {
			$_storeID=$_stores[$_languageCode]->getId();
		} else {
			$_storeID=0; // default store for no language match
		}		
		
		$this->set('languagecode',$_languageCode);
		$this->set('storeid',$_storeID);
		$this->set('baseurlmedia',$baseUrlMedia);		
	}	

	// -- load translator
	//		
	private function loadTranslator()
	{

		// load app translator			
		$_languageCode=$this->get('languagecode');
		
		$this->__t=new \PAJ\Library\Language\Translator($_languageCode);
	}
		
	protected function minify($_html,$_dev=false,$_sanitize=true)
	{
		if (!$_dev) {
			// minify live code
			return ($_sanitize ? $this->sanitize(\PAJ\Library\Minify\HTML::minify($_html,array('jsCleanComments' => true))) : \PAJ\Library\Minify\HTML::minify($_html,array('jsCleanComments' => true)));
		
		} else {
		
			return $_html;
		}
	}
	
	protected function sanitize($_html) {
	   
		return (preg_replace('#\R+#', ' ', $_html));
	}
	
	public function truncate($_text,$_length=25,$_raw=false)
	{
		$_truncatedText=$_text;

		if (strlen($_text) > $_length) { $_truncatedText=preg_replace('/\s+?(\S+)?$/', '', substr($_text, 0, $_length)). '...'; }

		if ($_raw)
		{
			return ($_truncatedText);
		}
			return (htmlentities($_truncatedText, ENT_QUOTES, "UTF-8"));

	}	
		
	protected function loadClassVariables($_variables)
	{
		foreach ($_variables as $_variableName=>$_variableData)
		{
			// check for optional data
			if (substr($_variableName, -8) === 'optional') { continue; }
			
			$_variableData=trim($_variableData);
			if (empty($_variableData)) {
				throw new \Exception('Class variable '. $_variableName. ' cannot be empty.');
			}
			
			$this->set($_variableName,$_variableData);
						
		}
	}	
	
	protected function debug($_data)
	{
		echo '<pre>';
		print_r($_data);
		echo '</pre>';
		exit;
	}	
	
	/**
	 * get function.
	 * @what class variable retriever
	 * @access public
	 * @return VARIABLE FROM ARRAY
	 */	
  	public function get($variable)
	{
		if (!isset($this->__[$variable]) && substr($variable, -8) != 'optional') { throw new \Exception(get_class($this). ' - The requested class variable "'. $variable. '" does not exist.');}
		
		return $this->__[$variable];
	}


	/**
	 * set function.
	 * @what class variable setter
	 * @access public
	 * @return VARIABLE TO ARRAY
	 */		
	public function set($key,$value)
	{
		$this->__[$key] = $value;
	}	
	
	
}