<?php
/**
 *  
 *  Copyright (C) 2014 paj@gaiterjones.com
 *
 * 	http://pe.terjon.es/index.php?ajax&class=PAJ_Library_Security_Encrypt&variables=data=data|mcryptkey=key
 *
 */
 
namespace PAJ\Library\Security;

class Encrypt{
 
 	protected $__;
	protected $__config;
 
	public function __construct($_variables) {
			
			$this->loadConfig();
			$this->loadClassVariables($_variables);
			$this->encryptData();
		
	}
	
	protected function encryptData()
	{
		
		// init
		$this->set('success',false);
		$this->set('output',false);
		$this->set('errormessage','Encrypt error.');
		
		try {
				$_key = $this->get('mcryptkey');
				$_string = $this->get('data');

				$_encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($_key), $_string, MCRYPT_MODE_CBC, md5(md5($_key))));
				
				$this->set('success',true);
				$this->set('output',array(
					'encrypted' => $_encrypted
				));					
			
			
		} catch (\Exception $e) {
			$this->set('errormessage','Error - '. $e);
		}

	}
	
	private function loadConfig()
	{
		if (!defined('ANS')) { throw new \Exception ('No configuration class specified. (SEC)'); }
		
		$_class = '\\PAJ\\Application\\'. ANS. '\\config';
		
		$this->__config= new $_class();
	}
	
	public function set($key,$value)
	{
		$this->__[$key] = $value;
	}
		
  	public function get($variable)
	{
		if (!isset($this->__[$variable]) && substr($variable, -8) != 'optional') { throw new \Exception(get_class($this). ' - The requested class variable "'. $variable. '" does not exist.');}
		
		return $this->__[$variable];
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
}

?>