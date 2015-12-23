<?php
/**
 *  
 *  Copyright (C) 2014 paj@gaiterjones.com
 *
 * 	
 * http://pe.terjon.es/index.php?ajax&class=PAJ_Library_Security_LastActive_SetLastActive&variables=dbnames=appsecurity|dbtables=users
 */

// Set Last Active User Session Timestamp
//
//

namespace PAJ\Library\Security\LastActive;

class GetLastActive extends LastActiveController {

	public function __construct($_variables) {

			parent::__construct();
			
			$this->loadClassVariables($_variables);
			
			$this->getLastActive();
		
	}
	
	protected function getLastActive()
	{
	
		// init
		$this->set('success',false);
		$this->set('errormessage','Invalid session.');
		
		$_userID=$this->get('userid');
		
		if ($_userID)
		{
			// query VARS
			$_numRows=0;
			$_queryResult=false;
			$_cacheNameSpace=false;
			$_dbName='appsecurity'; // use app DB
			$_useCacheforQuery=false;
			$_cacheTTL=172800;
			
			$_query='SELECT * FROM users WHERE userid="'. $_userID. '"';
				$_obj=new \PAJ\Library\DB\MYSQL\QueryAllRows($_query,$_cacheNameSpace,$_dbName,$_useCacheforQuery,$_cacheTTL);
					$_queryResult=$_obj->get('queryresult');
					$_numRows=$_obj->get('queryrows');
				
					
			if ($_numRows) {
			
				$this->set('success',true);
				$this->set('output',array('getLastActive' =>$_queryResult, 'numrows' => $_numRows, 'output' => 'Session getactive timestamp retrieved.'));
			} else {
				$this->set('errormessage','Error obtaining last active timestamp.');
			}
			
		}
		
		
	}
}
