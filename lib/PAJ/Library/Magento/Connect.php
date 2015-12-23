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
 /**
 * Main MAGENTO class
 * -- Connects to MAGENTO
 * @access public
 * @return nix
 */
namespace PAJ\Library\Magento;
class Connect
{

	protected $__config;
	protected $__;
	
	public function __construct() {
		
			$this->loadConfig();
			$this->loadMagento();

	}
	
	
	// -- get app config
	private function loadConfig()
	{
		if (!defined('ANS')) { throw new \Exception ('No configuration class specified. (SEC)'); }
		
		$_class = '\\PAJ\\Application\\'. ANS. '\\config';
		
		$this->__config= new $_class();
	}



	// -- connect to Magento
	private function loadMagento()
	{
		if (file_exists($this->__config->get('pathToMagentoApp'))) {
			
			include_once($this->__config->get('pathToMagentoApp'));
			
		} else if (file_exists(ROOT_DIR . $this->__config->get('pathToMagentoApp'))) {
			
			include_once(ROOT_DIR . $this->__config->get('pathToMagentoApp'));
			
		} else {

			throw new \Exception('Mage NOT Found at '. $this->__config->get('pathToMagentoApp'));
		}
		
		umask(0);
		\Mage::app();
		\Mage::app()->loadArea(\Mage_Core_Model_App_Area::AREA_FRONTEND);
		
		$baseUrlMedia = \Mage::getBaseUrl(\Mage_Core_Model_Store::URL_TYPE_MEDIA);
		
		$this->set('baseurlmedia',$baseUrlMedia);		
	}
	
	protected function loadClassVariables($_variables)
	{
		foreach ($_variables as $_variableName=>$_variableData)
		{
			// check for optional data
			if (substr($_variableName, -8) === 'optional') { continue; }
			
			$_variableData=trim($_variableData);
			if ($_variableData !== '0' && empty($_variableData)) {
				throw new \Exception('Class variable '.$_variableName. ' cannot be empty.');
			}
			
			$this->set($_variableName,$_variableData);
						
		}
	}	
	
	public function set($key,$value)
	{
		$this->__[$key] = $value;
	}

	public function get($variable)
	{
		return $this->__[$variable];
	}

}  
?>