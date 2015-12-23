<?php
/**
 *  
 *  Copyright (C) 2014 paj@gaiterjones.com
 *
 * 	http://pe.terjon.es/index.php?ajax&class=PAJ_Library_Security_Encrypt&variables=data=PASSWORD
 *
 */
namespace PAJ\Library\Security;

class Decrypt{
 
 	protected $__;
	protected $__config;
 
	public function __construct($_variables) {
			
			$this->loadConfig();
			$this->loadClassVariables($_variables);
			$this->decryptData();
		
	}
	
	protected function decryptData()
	{
		
		// init
		$this->set('success',false);
		$this->set('output',false);
		$this->set('errormessage','Decrypt error.');
		
		try {
				$_key = $this->__config->get('mcryptkey');
				$_encrypted = $this->get('data');

				$_decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($_key), base64_decode($_encrypted), MCRYPT_MODE_CBC, md5(md5($_key))), "\0");
				
				$this->set('decrypted',$_decrypted);
				
				$this->set('success',true);
				$this->set('output',array(
					'decrypted' => $_decrypted
				));					
			
			
		} catch (\Exception $e) {
			$this->set('errormessage','Error - '. $e);
		}

	}
	
    public function __toString()
    {
        return $this->get('decrypted');
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