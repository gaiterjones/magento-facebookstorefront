<?php
/**
 *  Application Security Password Reset Request
 *
 *  Copyright (C) 2014
 *
 *  http://pe.terjon.es/index.php?ajax&class=PAJ_Library_Security_PasswordResetRequest&variables=form_1_user_password_reset_email=paj@p-a-j.co.uk
 *  @who	   	PAJ
 *  @info   	paj@gaiterjones.com
 *  @license    blog.gaiterjones.com
 * 	
 *
 */
namespace PAJ\Library\Security;

class PasswordResetRequest extends \PAJ\Library\Security\SecurityController
{
	
	public function __construct($_variables) {

		parent::__construct();
		
		$_SESSION['errormessage']='undefined';

		$this->loadClassVariables($_variables);	

		$this->passwordResetRequest();

	}
	
	/**
	 * passwordReset function.
	 * @what - Process via ajax password reset form to create a password reset token and link that are emailed to the user
	 * @access private
	 * @return void
	 */
	private function passwordResetRequest()
	{		
		$this->set('success',false);
		$this->set('output',false);
		$_userID=false;
		
		// vars
		$_userEmail=$this->get('form_1_user_password_reset_email');
		
		if ($this->DBValidateUserEmail($_userEmail)) // validate email against activated local account
		{
			$_userID=$this->get('userid');
			
			if ($_userID != false) // validated userid from db
			{

				if ($this->DBPasswordResetRequestsCount($_SERVER['REMOTE_ADDR'])) // validated no DOS/Too many requests from IP
				{
					$_token=$this->newLoginCookieToken($_userID);
					
					$this->DBPasswordResetRequestsInsert($_userID,$_userEmail,$_token);
					
					$_confirmationLink=$this->__config->get('applicationURL'). '?page=user-passwordchange&resetpassword&token='.$_token. '&email='.$_userEmail;
					
					if ($this->sendPasswordResetEmail($_userEmail,$_confirmationLink,$_token)) // send email
					{
						$this->set('success',true);
						$this->set('output',$this->__t->__('Password reset instructions have been sent to'). ' '. $_userEmail);	
						return;
						
					} else {
					
						$this->set('errormessage',$this->__t->__('Error sending reset information email.'));
						return;				
					}
					
				} else {
					
					$this->set('errormessage',$this->__t->__('Too many password reset requests from this IP address.'));
					return;
				}
			}
		
		}
		
		$this->set('errormessage',$this->__t->__('This email address was not found.'));
	}
	
	/**
	 * DBPasswordResetRequestsCount function.
	 * @what - count the number of requests by IP address to control DOS etc.
	 * @access private
	 * @param mixed $_ip
	 * @return void
	 */
	private function DBPasswordResetRequestsCount($_ip)
	{		
		$_query = "SELECT COUNT(*) FROM passwordresetrequests WHERE ip = '". $_ip. "'";
		
		$_obj=new \PAJ\Library\DB\MYSQL\Query($_query,false,$this->get('dbname'));
		
		if (!$_obj->get('queryresult')) throw new \Exception('Query failed: '. $_query);		

		$_queryResult=$_obj->get('queryresult');
			unset($_obj);
			
		if ($_queryResult)
		{
			$_requestCount=$_DBData['COUNT(*)'];
			
			if ((int)$_requestCount > 3) { return false; }
		
		}
		
		return true;
	}

	/**
	 * DBPasswordResetRequestsInsert function.
	 * @what - insert a password reset request entry in the database
	 * @access private
	 * @param mixed $_userID
	 * @param mixed $_userEmail
	 * @param mixed $_token
	 * @return void
	 */
	private function DBPasswordResetRequestsInsert($_userID,$_userEmail,$_token)
	{	

		$_numRows=0;						// init numrows
		$_queryResult=false;				// init queryresult
		$_insert=true;						// insert query true/false
		$_cacheNameSpace=false; 			// namespace true, use app namespace false
		$_dbName=$this->get('dbname');		// database name
		$_incrementCacheNameSpace=false; 	// cache increment BOO
		
		$_query="INSERT INTO passwordresetrequests (userid, email, token, ip) VALUES ('". $_userID. "', '". $_userEmail. "', '". $_token. "', '". $_SERVER['REMOTE_ADDR']. "')";
		$_obj=new \PAJ\Library\DB\MYSQL\Query($_query,$_insert,$_dbName,$_cacheNameSpace,$_incrementCacheNameSpace);
			if (!$_obj->get('queryresult')) throw new \Exception('Query failed: '. $_query);
				unset($_obj);
	}		
	/**
	 * sendPasswordResetEmail function.
	 * @what - send an email with password reset instructions using an email template file
	 * @access private
	 * @param mixed $_email
	 * @param mixed $_confirmationLink
	 * @return void
	 */
	private function sendPasswordResetEmail($_email,$_confirmationLink,$_token)
	{
		$_subject=$this->__t->__('Lost your password?');
		
		// edit template
		$_template=file_get_contents('/root/Dropbox/paj/www/dev/PAJ/Library/Email/template/password_reset_email_'.$this->__t->__('en'). '.html', FILE_USE_INCLUDE_PATH);
		// subject
		$_template=str_replace('*|SUBJECT|*',$_subject,$_template);
		// www path for images
		$_template=str_replace('*|WWWURL|*',$this->__config->_serverURL,$_template);
		
		// $_template=str_replace('*|1|*',$_firstName,$_template);
		$_template=str_replace('*|2|*','Your password reset token is :<br>'. $_token. '<br>'. $_confirmationLink,$_template);
		$_template=str_replace('*|3|*',$_email,$_template);
		
		
		// create confirmation email
		$_obj=new \PAJ\Library\Email\EmailController(array(
		  'to'  => $_email,
		  'from' => $this->__config->get('appName'). ' <passwords@gaiterjones.com>',
		  'subject' => $_subject,
		  'body' => $_template,
		  'cc' => '',
		  'bcc' => '',
		));
		
		$_success=$_obj->get('emailsuccess');
		unset($_obj);
		
		if ($_success) { return true; }

		return false;
		
	}
	
	private function loadClassVariables($_variables)
	{
		foreach ($_variables as $_variableName=>$_variableData)
		{
			$_variableData=trim($_variableData);
			if (!isset($_variableData)) {
				// friendly message for ajax
				$_SESSION['errormessage']=$this->__t->__('Please complete the '.  ucfirst(str_replace('_', ' ', $_variableName)). ' field.');

				throw new \Exception(get_class($this).' class variable '.$_variableName. ' cannot be empty.');
			}
			
			$this->set($_variableName,$_variableData);
						
		}
	}

}
