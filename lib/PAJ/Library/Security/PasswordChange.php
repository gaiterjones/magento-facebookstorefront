<?php
/**
 *  
 *  Copyright (C) 2014 paj@gaiterjones.com
 *
 *  http://pe.terjon.es/index.php?ajax&class=PAJ_Library_Security_PasswordChange&variables=user_change_password_form_password=password|user_change_password_form_confirm_password=password|user_change_password_form_token=becf6f4d6924462a592bf3d7dfe6ce7537b9825b29d56f68260107eb9c94da89
 */
namespace PAJ\Library\Security;

/**
 * PasswordReset class.
 * CHANGE a users password
 * @extends SecurityController
 */
class PasswordChange extends \PAJ\Library\Security\SecurityController

{
	  
	public function __construct($_variables) {

		parent::__construct();
		
		$_SESSION['errormessage']='undefined';
		
		$this->loadClassVariables($_variables);
		
		$this->passwordChange();
	}
	
	/**
	 * userChangePasswordFormSubmit function.
	 * @what - process via ajax password change form to validate password reset token and change the password
	 * @access private
	 * @return void
	 */
	private function passwordChange()
	{		
		$this->set('success',false);
		$this->set('output',false);
		
		// vars
		$_userID=false;
		$_userPassword=$this->get('user_change_password_form_password');
		$_userConfirmPassword=$this->get('user_change_password_form_confirm_password');
		$_resetPasswordToken=$this->get('user_change_password_form_token');
		
		// validate passwords match and size
		$_userPassword=trim($_userPassword);
		if (strlen($_userPassword) < 6 ) { throw new \Exception($this->__t->__('Password is too short, minimum 6 characters.')); }
		if ($_userPassword != $_userConfirmPassword) { throw new \Exception($this->__t->__('The passwords do not match!')); }
		
		// get user id from token
		
		if ($this->DBPasswordResetUserValidate($_resetPasswordToken))
		{
			$_success=false;
			$_userID=$this->get('passwordresetuserid');
			$_userFullName=$this->get('passwordresetfullname');
			
			// generate password hash
			$_hash=$this->generateHash($_userPassword,$_userID,$_userFullName);
			
			// update users password
			if ($this->DBUserPasswordUpdate($_userID,$_hash))
			{
				// tidy up
				
				// delete password change token it is no longer valid
				$this->DBPasswordResetTokenDelete($_resetPasswordToken,$_userID);
				
				// delete any user persistent login sessions, they are no longer valid
				$this->DBLoginCookieTokenDelete($_userID);
				
				$this->set('success',true);
				$this->set('output',$this->__t->__('Your password has been changed.'));	
				return;					
			
			} else {
			
				$this->set('errormessage',$this->__t->__('Error - could not change your password.'));
				return;			
			}
			
	
		
		} else {
		
			$this->set('errormessage',$this->__t->__('Error - could not validate user account with change password token.'));
			return;
		}
	
	}	

	/**
	 * userResetPasswordTokenValidate function.
	 * @what - Validate the reset token against the database
	 * @access public
	 * @return void
	 */
	public function userResetPasswordTokenValidate()
	{
		$_token=$this->get('user_password_change_token');
		$_userEmail=$this->get('user_password_change_email');
		
		if ($this->DBPasswordResetRequestsValidate($_userEmail,$_token)) { return true; }
		
		
		return false;
	}

	/**
	 * generatePasswordResetToken function.
	 * @what - generate a token to use as authentication for the reset request
	 * @access private
	 * @param mixed $_userID
	 * @return void
	 */
	private function generatePasswordResetToken($_userID)
	{		
		$_token=hash('sha256',time(). uniqid() . $_userID);
		return $_token;
	}
	
	/**
	 * DBUserPasswordUpdate function.
	 * @what - store a password hash in the database
	 * @access private
	 * @param mixed $_userid
	 * @param mixed $_hash
	 * @return void
	 */
	private function DBUserPasswordUpdate($_userid,$_hash)
	{
		
		$_queryResult=false;
		$_numRows=0;
		$_queryResult=false;
		$_insert=true;
		$_cacheNameSpace=false;
		$_DBName=$this->get('dbname');
		$_incrementCacheNameSpace=false;
		
		$_query="UPDATE users SET hash = '". $_hash. "' WHERE (userid='". $_userid. "')";
			$_obj=new \PAJ\Library\DB\MYSQL\Query($_query,$_insert,$_DBName,$_cacheNameSpace,$_incrementCacheNameSpace);
				$_queryResult=$_obj->get('queryresult');
					unset($_obj);
					
		return $_queryResult;
		
	}	


	/**
	 * DBPasswordResetTokenDelete function.
	 * @what - delete the reset token from the database after it has been used
	 * @access public
	 * @param mixed $_token
	 * @param mixed $_userID
	 * @return void
	 */
	public function DBPasswordResetTokenDelete($_token,$_userID)
	{
		$_queryResult=false;
		$_numRows=0;
		$_queryResult=false;
		$_insert=true;
		$_cacheNameSpace=false;
		$_DBName=$this->get('dbname');
		$_incrementCacheNameSpace=false;
		
		// delete just the current request and token OR -->>
		//$_query="DELETE FROM passwordresetrequests WHERE (userid='".$_userID."' AND token='". $_token. "')";
		
		// delete all requests from this user after a succesful change - more secure???
		
		$_query="DELETE FROM passwordresetrequests WHERE (userid='".$_userID."')";
			$_obj=new \PAJ\Library\DB\MYSQL\Query($_query,$_insert,$_DBName,$_cacheNameSpace,$_incrementCacheNameSpace);
				$_queryResult=$_obj->get('queryresult');
					unset($_obj);
					
			return $_queryResult;
	}
	
	/**
	 * DBPasswordResetUserValidate function.
	 * @what - validate user from the password token returning user credentials from database
	 * @access private
	 * @param mixed $_token
	 * @return void
	 */
	private function DBPasswordResetUserValidate($_token)
	{
		/**
		 * _query
		 * @what - get user details by joiing user table with password reset table
		 * @var mixed
		 * @access private
		 */
		 
		$_queryResult=false;
		$_numRows=0;
		$_cacheNameSpace=false;
		$_dbName=$this->get('dbname');
		$_useCacheforQuery=false;
		$_cacheTTL=172800;		
		
		$_query =  "SELECT *
					FROM users
					INNER JOIN passwordresetrequests ON passwordresetrequests.userid=users.userid
					WHERE passwordresetrequests.token = '". $_token. "'";
					
			$_obj=new \PAJ\Library\DB\MYSQL\QueryAllRows($_query,$_cacheNameSpace,$_dbName,$_useCacheforQuery,$_cacheTTL);
				$_queryResult=$_obj->get('queryresult');
				$_numRows=$_obj->get('queryrows');
					unset($_obj);
			
			if ($_numRows > 0) // results found
			{
				foreach($_queryResult as $_key => $_rows);
				
				$this->set('passwordresetuserid',$_rows['userid']);
				$this->set('passwordresetfullname',$_rows['name']);				
				return true;
				
			} else { // no records found
			
				return false;
			}		
	}
	
	/**
	 * DBPasswordResetRequestsValidate function.
	 * @what - validate the password reset token and email in the database
	 * @access private
	 * @param mixed $_userEmail
	 * @param mixed $_token
	 * @return void
	 */
	private function DBPasswordResetRequestsValidate($_userEmail,$_token)
	{		
		$_queryResult=false;
		$_numRows=0;
		$_cacheNameSpace=false;
		$_dbName=$this->get('dbname');
		$_useCacheforQuery=false;
		$_cacheTTL=172800;	
		
		$_query="SELECT * FROM passwordresetrequests WHERE (email = '". $_userEmail. "' AND token = '". $_token. "') LIMIT 1";
			
			$_obj=new \PAJ\Library\DB\MYSQL\QueryAllRows($_query,$_cacheNameSpace,$_dbName,$_useCacheforQuery,$_cacheTTL);
				$_queryResult=$_obj->get('queryresult');
				$_numRows=$_obj->get('queryrows');
					unset($_obj);
					
			if ($_numRows > 0) // results found
			{
				return true;
				
			} else { // no records found
				return false;
			}			
	}


	/**
	 * loadClassVariables function.
	 * @what - load class variables for this object
	 * @access private
	 * @param mixed $_variables
	 * @return void
	 */
	private function loadClassVariables($_variables)
	{
		foreach ($_variables as $_variableName=>$_variableData)
		{
			// check for optional data
			if (substr($_variableName, -8) === 'optional') { continue; }
			
			$_variableData=trim($_variableData);
			if (empty($_variableData)) {
			
				// friendly message for ajax forms
				$_SESSION['errormessage']=$this->__t->__('Please complete the '.  ucfirst(str_replace('post_object_', ' ', $_variableName)). ' field.');
				
				throw new exception('The requested class variable - '. $_variableName. ' cannot be empty.');
			
			}
			
			$this->set($_variableName,$_variableData);
						
		}
	}
}
