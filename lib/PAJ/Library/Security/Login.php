<?php
/**
 *  Application Security Login
 '
 *  Copyright (C) 2014
 *
 *
 *  @who	   	PAJ
 *  @info   	paj@gaiterjones.com
 *  @license    blog.gaiterjones.com
 * 	
 *
 */

namespace PAJ\Library\Security;

class Login extends \PAJ\Library\Security\SecurityController
{
	
	public function __construct($_variables) {

		parent::__construct();
		
		$_SESSION['errormessage']='undefined';
		
		$this->loadClassVariables($_variables);		
		
		try { // and login
			// -- check for failed attempts in memcache
			//
			if ($this->__config->get('blockFailedAttempts')) {
				$_attempts=$this->getLogCounter($this->applicationName().md5($_SERVER['REMOTE_ADDR']));
				if ( $_attempts > 3) { throw new \Exception($this->__t->__('Access disabled due to multiple failed login attempts.')); }
			}
			
			// get user data and validate login
			if ($this->getUser())
			{
				$this->validateUserLogin();
				
			} else {
			
				$this->set('output',$this->__t->__('Invalid Login.'));
				$this->set('errormessage',$this->__t->__('Invalid Login'));
				
				if ($this->__config->get('blockFailedAttempts')) {
					if ($this->incLogCounter($this->applicationName().md5($_SERVER['REMOTE_ADDR'])) > 3) { $this->set('errormessage',$this->__t->__('Too many failed attempts - access disabled.')); }
				}
				
				$this->set('success',false);
			}
		}
		catch (\Exception $e)
	    {			
			throw new \Exception ('Could not login - '. $e);
	    }		
	}
	
	// -- get user data from database
	//
	private function getUser()
	{
		
		$_query="SELECT * FROM users WHERE email = '". $this->get('login_form_username'). "' AND application='". $this->applicationName() . "' LIMIT 1";
		
		$_obj=new \PAJ\Library\DB\MYSQL\Query($_query,false,$this->get('dbname'));
		
		if ($_obj->get('queryresult'))
		{
			$this->set('db_userdata',$_obj->get('queryresult'));
			unset($_obj);
			return true;
		
		} else {
			unset($_obj);
			return false;
		}
	
	}

	// -- validate login data
	//
	private function validateUserLogin()
	{		
		$_output='';
		$this->set('success', false);
		
		$_loginFormUsername=$this->get('login_form_username');
		$_loginFormPassword=$this->get('login_form_password');
		$_loginFormRememberMe=$this->get('login_form_remember_me');
		
		// validate account active
		if (empty($_loginFormPassword))
		{
			$this->set('output',$this->__t->__('Empty Password.'));
			$this->set('errormessage',$this->__t->__('Empty Password.'));
			return;
		}

		$_DBData = $this->get('db_userdata');
	
		$_accountActivated=$_DBData['activated'];
		$_accountType=$_DBData['accounttype'];
		
		// validate account active
		if (!$_accountActivated && $_accountType === 'local')
		{
			$this->set('output',$this->__t->__('This account has not been activated.'));
			$this->set('errormessage',$this->__t->__('This account has not been activated.'));
			return;
		}
		
		// validate password http://alias.io/2010/01/store-passwords-safely-with-php-and-mysql/

		if (crypt($_loginFormPassword, $_DBData['hash']) == $_DBData['hash'] ) { // password validated
		
			$_originatingpage=false;
			
			// redirect to originating page
			if (isset($_SESSION["originatingpage"])) {
				$_originatingpage = $_SESSION["originatingpage"];
				unset($_SESSION["originatingpage"]);
			} else if(!isset($_COOKIE["originatingpage"])) {
				$_originatingpage = $_COOKIE["originatingpage"];
				setcookie("originatingpage", "", time() - 3600);
			}
			
			$this->set('output',
				array('output' => $this->__t->__('Login validated'), 'originatingpage' => $_originatingpage)
			);
			
			$this->set('success',true);
			
			// create session data from user data
			//
			$this->sessionBuild($_DBData,'session');
			
			// process remember me request from login form
			//
			if ($_loginFormRememberMe==='true')
			{
				// generate new authentication series number for every valid login with remember me set
				$_cookieSeries=$this->newLoginCookieSeries();
				
				// generate new login cookie token
				$_cookieToken=$this->newLoginCookieToken($_DBData['name']);
				
				// save authentication data to database
				$this->DBAuthenticationDataSet($_DBData['userid'],$_cookieSeries,$_cookieToken);
				
				// generate cookie data
				$_loginCookieData=$_DBData['userid'].'-'.$_cookieSeries.'-'.$_cookieToken;
				
				// create new auth cookie
				$this->setAuthCookie($_loginCookieData);				
				
			}
			
			// update last active timestamp
			$this->updateLastActiveTimestamp();
			
			// logged in
			

			
			return;

		} else { // incorrect password
		
			$this->set('output','Incorrect password');
			$this->set('errormessage',$this->__t->__('Invalid Login - #'). $this->incLogCounter($this->applicationName().md5($_SERVER['REMOTE_ADDR'])));
			
			if ($this->__config->get('blockFailedAttempts')) {
				if ($this->incLogCounter($this->applicationName().md5($_SERVER['REMOTE_ADDR'])) > 3) { $this->set('errormessage',$this->__t->__('Too many failed attempts - access disabled.')); }
			}
			
			$this->set('success',false);
			return;
		}

			

	}


	/**
	 * updateLastActiveTimestamp function.
	 * @what updates a timestamp in user database
	 */		
	private function updateLastActiveTimestamp()
	{
		$_obj=new \PAJ\Library\Security\LastActive\SetLastActive(array('dbnames' => 'appsecurity','dbtables' => 'users', 'dbcolumns' => 'userid'));
		unset($_obj);
	}
	
	/**
	 * incLogCounter function.
	 * @what increments a counter in memcache
	 * @access private
	 * @return INTEGER COUNTER
	 */		
	private function incLogCounter($_cacheNameSpace)
	{
		$this->__cache->increment($_cacheNameSpace);
		return ($this->getLogCounter($_cacheNameSpace));
	}
	
	/**
	 * getLogCounter function.
	 * @what gets a memcache counter used to numerate logs
	 * @access protected
	 * @return INTEGER COUNTER
	 */	
	protected function getLogCounter($_cacheNameSpace)
	{
	
		$_counter = $this->__cache->cacheGet($_cacheNameSpace); // get version from cache
        
        if ($_counter === false) { // if namespace not in cache reset to 1
            $_counter = 1;
            $this->__cache->cacheSet($_cacheNameSpace, $_counter,7200); // save to cache note ttl in seconds
        }
        
        return $_counter;
        
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
