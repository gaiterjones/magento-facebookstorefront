<?php
/**
 *  
 *  Copyright (C) 2015 paj@gaiterjones.com
 *
 * 	Add an IP to Apache Ban List
 *
 */
namespace PAJ\Library\Security;

class BanIP{
 
 	protected $__;
 
	public function __construct($_variables) {
			
			$this->loadClassVariables($_variables);
			$this->banIP();
	}
	
	protected function banIP()
	{
		
		// init
		$this->set('success',false);
		$this->set('output',false);
		$this->set('errormessage','Ban Ip undefined error.');
		
		try {
				$_client=$_SERVER['REMOTE_ADDR'];
				
				if ($_client != '217.86.225.24') // ban only allowed from this ip
				{
					$this->set('errormessage','Ban IP - Access Denied.');
					return;
				}
				
				$_ip = $this->get('ip');
				$_comment=$this->get('comment');
				$_count=0;
				
				$_banFile = "/home/www/conf/IPBanList.conf";
				
				$_fh = fopen($_banFile, 'a');
				
				if ($_fh) {
					
					while (($_line = fgets($_fh)) !== false) {
						// read file
						
					}					
					
					fwrite($_fh, '# '. date("D M j G:i:s T Y"). ' : '. $_comment. "\n". $_ip. ' -'. "\n");
					
					fclose($_fh);
					
				} else {
					$this->set('errormessage','Could not open ban file for editing.');
					return;
				}
				
				$this->set('success',true);
				$this->set('output',array(
					'banIP' => $_ip,
					'client' => $_client
				));					
			
			
		} catch (\Exception $e) {
			$this->set('errormessage','Error - '. $e);
		}

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