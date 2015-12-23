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

namespace PAJ\Library\Security\LastActive;

class LastActiveController {

	protected $__;
	protected $__config;

	public function __construct() {
		
		$this->loadConfig();
												
	}

	// loads config
	//
	private function loadConfig()
	{
		if (!defined('ANS')) { throw new \Exception ('No configuration class specified. (CACHE)'); }
		
		$_class = '\\PAJ\\Application\\'. ANS. '\\config';
		
		$this->__config= new $_class();
	}
	

	protected function loadClassVariables($_variables)
	{
		foreach ($_variables as $_variableName=>$_variableData)
		{
			// check for optional data
			if (substr($_variableName, -8) === 'optional') { continue; }
			
			$_variableData=trim($_variableData);
			if (empty($_variableData)) {
				throw new \Exception('Class variable '.$_variableName. ' cannot be empty.');
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