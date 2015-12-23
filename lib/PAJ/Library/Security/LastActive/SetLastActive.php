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

class SetLastActive extends LastActiveController {

	public function __construct($_variables) {

			parent::__construct();
			
			$this->loadClassVariables($_variables);
			
			$this->setLastActive();
		
	}
	
	protected function setLastActive()
	{
	
		// init
		$this->set('success',false);
		$this->set('errormessage','Invalid session.');
		
		$_userID=false;
		
		if (isset($_SESSION['userid'])) {$_userID=$_SESSION['userid'];}
		
		if ($_userID)
		{
				
			// query VARS
			$_numRows=0;
			$_queryResult=false;
			$_insert=true;
			$_cacheNameSpace=false;
			$_incrementCacheNameSpace=false;
			
			
			$_dbnames=$this->get('dbnames');
			$_dbtables=$this->get('dbtables');
			$_dbcolumns=$this->get('dbcolumns');
			
			$_dbnames=explode(',',$_dbnames);
			$_dbtables=explode(',',$_dbtables);
			$_dbcolumns=explode(',',$_dbcolumns);
			
			foreach ($_dbnames as $_key => $_dbname)
			{
				
				$_query='UPDATE '. $_dbtables[$_key]. ' SET timeStamp=NOW() WHERE '. $_dbcolumns[$_key]. '="'. $_userID. '"';
					$_obj=new \PAJ\Library\DB\MYSQL\Query($_query,$_insert,$_dbname,$_cacheNameSpace,$_incrementCacheNameSpace);
					$_queryResult=$_obj->get('queryresult');
				unset($_obj);
				
			}
						
			if ($_queryResult) {
			
				$this->set('success',true);
				$this->set('output',array('setLastActive' =>true, 'output' => 'Session lastactive timestamp updated.'));
			} else {
				$this->set('errormessage','Error updating database with last active timestamp.');
			}
			
		}
		
		
	}
}
