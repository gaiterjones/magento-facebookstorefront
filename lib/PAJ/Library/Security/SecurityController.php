<?php
/**
 *  PAJ\Library
	Security Controller Class
 '
 *  Copyright (C) 2014
 *
 *
 *  @who	   	PAJ
 *  @info   	paj@gaiterjones.com
 *  @license    blog.gaiterjones.com
 * 	
 *
 
 
  *  http://jaspan.com/improved_persistent_login_cookie_best_practice
  
When the user successfully logs in with Remember Me checked, a login cookie is issued in addition to the standard session management cookie.[2]
The login cookie contains the user's username, a series identifier, and a token. The series and token are unguessable random numbers from a suitably large space. All three are stored together in a database table.
When a non-logged-in user visits the site and presents a login cookie, the username, series, and token are looked up in the database.
If the triplet is present, the user is considered authenticated. The used token is removed from the database. A new token is generated, stored in database with the username and the same series identifier, and a new login cookie containing all three is issued to the user.
If the username and series are present but the token does not match, a theft is assumed. The user receives a strongly worded warning and all of the user's remembered sessions are deleted.
If the username and series are not present, the login cookie is ignored.
It is critical that the series identifier be reused for each token in a series. If the series identifier were instead simply another one time use random number, the system could not differentiate between a series/token pair that had been stolen and one that, for example, had simply expired and been erased from the database.
 */
namespace PAJ\Library\Security;

class SecurityController
{
	protected $__;
	protected $__config;
	protected $__cache;
	public $__t; 	
	
	// -- constructor
	//
	public function __construct() {

		$this->loadConfig();
		
		if ($this->__config->get('securityEnabled')) // is security required ???
		{		
			$this->loadMemcache();
			$this->loadTranslator();
			$this->sessionStart();
			
			if ($this->get('loggedin')) {

				// Check for SSL and redirect for logged in sessions
				if ($this->__config->get('SSLLogin')) {
					if (!isset($_SERVER['HTTPS']) || !$_SERVER['HTTPS']) {		
					   //header("HTTP/1.1 301 Moved Permanently");
					   header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
					   exit();
					}
				}			
				
				$this->sessionLoad();			
			}
		}
	
	}

	// -- destructor
	//	
	public function __destruct()
	{
		unset($this->__config);
		unset($this->__cache);
		unset($this->__);
		unset($this->__t);
	}	

	// -- start the user session
	//		
	protected function sessionStart()
	{
		// -- process logout
		//
		if(isset($_GET['logout'])){ $_logOut = true;} else { $_logOut = false;}	
		if ($_logOut) {
			$this->destroyUserSession($this->getLoginCookie());
			return;
		}
		
		// -- start session
		//
		if (!isset($_SESSION)) {
			session_set_cookie_params(7200,"/"); // session cookie lifetime 2 hours
			session_start();
			
		}

		// -- login via session or persistent login authentication cookie
		//
		if (isset($_SESSION[$this->applicationName(). '_LOGGED_IN'])) { // session login
			
			$this->set('loggedin', $_SESSION[$this->applicationName(). '_LOGGED_IN']);
			$_SESSION['loggedinvia'] = 'session';
			
		} else if (isset($_COOKIE[$this->applicationName(). '_AUTH_TOKEN'])) { // cookie login

			$this->set('loggedin',$this->validateLoginCookie($_COOKIE[$this->applicationName(). '_AUTH_TOKEN']));
		}	

	}
	
	// -- load and validate the user session
	//	
	protected function sessionLoad()
	{
		if (isset($_SESSION['userid'])) {$_userID=$_SESSION['userid'];}
		if (isset($_SESSION['sessionkey'])) {$_sessionKey=$_SESSION['sessionkey'];}
		
		// -- validate session is authentic with session key
		//
		if ($_sessionKey != md5($_userID)) {
			$this->destroyUserSession(); // log user out
			throw new \Exception($this->__t->__('Invalid session key.'));
		}
		
		$this->set('userid',$_userID);
		$this->set('sessionkey',$_sessionKey);
		if (isset($_SESSION['firstname'])) {$this->set('firstname', $_SESSION['firstname']);}
		if (isset($_SESSION['fullname'])) {$this->set('fullname', $_SESSION['fullname']);}
	
	}
	
	// sanitize app name
	protected function applicationName()
	{
		return str_replace('.','_',$this->get('applicationname'));
	}
	
	// -- get the login auth cookie
	//		
	public function getLoginCookie()
	{
		if (isset($_COOKIE[$this->applicationName(). '_AUTH_TOKEN'])) { return ($_COOKIE[$this->applicationName(). '_AUTH_TOKEN']); }
		
		return false;
	}

	// -- validate the login cookie
	//		
	protected function validateLoginCookie($_loginCookie)
	{	
		
		// -- extract cookie data
		//
		$_cookieData=explode('-',$_loginCookie);
		
		$_loginCookieUsername=$_cookieData[0];
		$_loginCookieSeries=$_cookieData[1];
		$_loginCookieToken=$_cookieData[2];
	
		// -- get authentication data from DB
		//
		$_query="SELECT * FROM authentication WHERE (userid = '". $_loginCookieUsername. "' AND loginCookieSeries = '". $_loginCookieSeries. "') LIMIT 1";
		$_obj=new \PAJ\Library\DB\MYSQL\Query($_query,false,$this->get('dbname'));
			$_DBData=$_obj->get('queryresult');
					unset($_obj);
	
		if ($_DBData) // authentication series for user found
		{
			if ($_loginCookieToken === $_DBData['loginCookieToken']) // cookie token validated
			{
				
				$_query="SELECT * FROM users WHERE userid = '". $_loginCookieUsername. "' AND application='". $this->applicationName() . "' LIMIT 1";
				$_obj=new \PAJ\Library\DB\MYSQL\Query($_query,false,$this->get('dbname'));
					$_DBData=$_obj->get('queryresult');
						unset($_obj);				
												
				if ($_DBData) // user found
				{				
					// -- create session data from user data
					//
					$this->sessionBuild($_DBData,'cookie');
					
					// -- generate new cookie token for series
					//
					$_newCookieToken=$this->newLoginCookieToken($_DBData['userid']);
					$_loginCookieData=$_DBData['userid'].'-'.$_loginCookieSeries.'-'.$_newCookieToken;
					
					// -- save new series token to database
					//
					$this->DBLoginCookieTokenSet($_DBData['userid'],$_loginCookieSeries,$_newCookieToken);
					
					// -- create new auth cookie
					//
					$this->setAuthCookie($_loginCookieData);
					
					// -- logged in via auth cookie
					//
					return true;
				
				} else { // user not found
					
					$this->destroyUserSession($_loginCookie);
					return false;
				
				}
		
		
			} else { // invalid token, cookie compromised, OR logged in elsewhere - throw dummy out of pram - remove all users stored authentication data.
			
				$this->destroyAllUserSessions($_loginCookie);
				throw new \Exception("WARNING: Your persistent login data is no longer valid, please login again.");
			
			}
		
		} else { // no authentication series for user found
		
			$this->destroyUserSession($_loginCookie);
			return false;
		}
	}	

	// -- build user session from user data
	//		
	public function sessionBuild($_userData,$_loggedInVia='session')
	{
		if (!isset($_SESSION)) {session_start();}
		
		// session data
		$_SESSION['firstname'] = $_userData['first_name'];
		$_SESSION['fullname'] = $_userData['name'];
		$_SESSION['userid'] = $_userData['userid'];
		$_SESSION['useremail'] = $_userData['email'];
		$_SESSION['sessionkey'] = md5($_userData['userid']);
		$_SESSION['languagecode'] = $_userData['locale'];
		$_SESSION['userprofileimage'] = $_userData['image'];
		$_SESSION['userlanguagepreference'] = $_userData['languagePreference'];
		$_SESSION['loggedinvia'] = $_loggedInVia;
		$_SESSION[$this->applicationName(). '_LOGGED_IN'] = true;
	
	}

	// -- destroy session and auth cookie
	//		
	public function destroyUserSession($_loginCookie=false)
	{
	
		if (!isset($_SESSION)) {session_start();}	
		session_destroy();
		
		if ($_loginCookie)
		{
			$this->DBLoginCookieTokenDelete($_loginCookie);
			$this->deleteAuthCookie(false);		
		}
		
		return true;
	}

	// -- destroy all session and perisitent login data
	//		
	protected function destroyAllUserSessions($_loginCookie=false)
	{
		if (!isset($_SESSION)) {session_start();}	
		session_destroy();

		if ($_loginCookie)
		{		
			$this->DBLoginCookieUserDelete($_loginCookie);
			$this->deleteAuthCookie(false);			
		}
	}	
	
	// -- create persistant login authentication cookie
	//
	protected function setAuthCookie($_loginCookieData)
	{
		setcookie(str_replace('.','_',$this->applicationName()). '_AUTH_TOKEN', $_loginCookieData, time()+60*60*24*365, '/', $this->get('applicationdomain'),false,false);
	}

	// -- delete persistant login authentication cookie
	//		
	protected function deleteAuthCookie($_db=true)
	{
		if (isset($_COOKIE[$this->applicationName(). '_AUTH_TOKEN'])) {
			
			if ($_db) {	$this->DBLoginCookieTokenDelete($_COOKIE[$this->applicationName(). '_AUTH_TOKEN']); }
			
			setcookie($this->applicationName(). '_AUTH_TOKEN', '', time() - 3600, '/', $this->get('applicationdomain'),false,false);
		}
	}

	// -- update persistant login data in DB
	//		
	protected function DBLoginCookieTokenSet($_userid,$_loginCookieSeries,$_loginCookieToken)
	{
		// update login cookie data
		$_query="UPDATE authentication SET loginCookieToken = '". $_loginCookieToken. "' WHERE (userid='".$_userid."' AND loginCookieSeries='".$_loginCookieSeries."')";
			$_obj=new \PAJ\Library\DB\MYSQL\Query($_query,true,$this->get('dbname'));
				unset($_obj);

	}

	// -- delete persistant login data from DB
	//	
	protected function DBLoginCookieUserDelete($_loginCookie)
	{
		$_cookieData=explode('-',$_loginCookie);
		
		$_loginCookieUsername=$_cookieData[0];
		$_loginCookieSeries=$_cookieData[1];
		$_loginCookieToken=$_cookieData[2];
		
		// delete login cookie series
		$_query="DELETE FROM authentication WHERE (userid='".$_loginCookieUsername."')";
		$_obj=new \PAJ\Library\DB\MYSQL\Query($_query,true,$this->get('dbname'));
		unset($_obj);
				
			return true;
	}


	// -- Delete the authentication token series from db
	//
	protected function DBLoginCookieTokenDelete($_loginCookie)
	{
		$_cookieData=explode('-',$_loginCookie);
		
		$_loginCookieUsername=$_cookieData[0];
		$_loginCookieSeries=$_cookieData[1];
		$_loginCookieToken=$_cookieData[2];
		
		// delete login cookie series
		$_query="DELETE FROM authentication WHERE (userid='".$_loginCookieUsername."' AND loginCookieSeries='".$_loginCookieSeries."')";
			$_obj=new \PAJ\Library\DB\MYSQL\Query($_query,true,$this->get('dbname'));
				unset($_obj);
				
			return true;
	}

	// -- build data for persistant authentication token
	//		
	protected function newLoginCookieToken($_Data)
	{
		$_loginCookieToken=hash('sha256',time(). uniqid() . $_Data);
		
		return $_loginCookieToken;
	}

	// -- build data for persistant authentication series
	//		
	protected function newLoginCookieSeries()
	{
		$_loginCookieSeries=$this->randomDigits(20);
		
		return $_loginCookieSeries;
	}

	protected function DBAuthenticationDataSet($_userid,$_loginCookieSeries,$_loginCookieToken)
	{
		// update login cookie data
		$_query="INSERT INTO authentication (userid, loginCookieSeries, loginCookieToken) VALUES ('". $_userid. "', '". $_loginCookieSeries. "', '". $_loginCookieToken. "')";
		$_obj=new \PAJ\Library\DB\MYSQL\Query($_query,true,$this->get('dbname'));	
			if (!$_obj->get('queryresult')) throw new \Exception('Query failed: '. $_query);
				unset($_obj);
	
	}
	
	/**
	 * DBValidateUserEmail function.
	 * @what - validate an email address with the DB
	 * @access private
	 * @param mixed $_userEmail
	 * @return void
	 */
	protected function DBValidateUserEmail($_userEmail)
	{	
		$_query="SELECT * FROM users WHERE email = '". $_userEmail. "' LIMIT 1";
		$_obj=new \PAJ\Library\DB\MYSQL\Query($_query,false,$this->get('dbname'));							
			$_DBData=$_obj->get('queryresult');
					unset($_obj);
				
		if ($_DBData) // results found
		{
			// check if account is activated
			//
			$_accountActivated=$_DBData['activated'];
			$_accountType=$_DBData['accounttype'];
			
			if ($_accountActivated AND $_accountType === 'local')
			{
				$this->set('userid',$_DBData['userid']);
				return true; // account validated with user email
			}			
			
			return false;
			
		} else { // no records found
		
			return false;
		}
	}

	// -- generate random digits
	//	
	private function randomDigits($numDigits) {
		if ($numDigits <= 0) {
			return '';
		}

		return mt_rand(0, 9) . $this->randomDigits($numDigits - 1);
	}
	
	protected function generateHash($_password,$_userID,$_username)
	{
		// A higher "cost" is more secure but consumes more processing power
		$_cost = 10;
		
		$_salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
		
		// Prefix information about the hash so PHP knows how to verify it later.
		// "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
		$_salt = sprintf("$2a$%02d$", $_cost) . $_salt;

		// Hash the password with the salt
		$_hash = crypt($_password, $_salt);
		
		return $_hash;
	}	

	// -- load config
	//		
	private function loadConfig()
	{
		if (!defined('ANS')) { throw new \Exception ('No configuration class specified. (SEC)'); }
		
		$_class = '\\PAJ\\Application\\'. ANS. '\\config';
		
		$this->__config= new $_class();
		
		// NOT LOGGED IN - default
		$this->set('loggedin', false);
		
		$_appName=$this->__config->get('applicationName');
		$_appName=str_replace(' ','',$_appName);
		$_appName=strtoupper($_appName);
	
		$this->set('applicationname', $_appName);
		$this->set('applicationdomain', $this->__config->get('applicationDomain'));
		$this->set('dbname','appsecurity');
	}

	// -- load translator
	//		
	private function loadTranslator()
	{
		// load app translator			
		if (isset($_SESSION['languagecode'])) { $_languageCode=$_SESSION['languagecode']; }
		
		if (empty($_languageCode)) { $_languageCode='en';}
		
		$this->__t=new \PAJ\Library\Language\Translator($_languageCode);
	}


	/**
	 * memcache loader function.
	 * @what loads memcache class
	 * @access private
	 * @return nix
	 */		
	private function loadMemcache()
	{
		$this->__cache=new \PAJ\Library\Cache\Memcache();
	}	
	  
	public function set($key,$value)
	{
		$this->__[$key] = $value;
	}
		
	public function get($variable)
	{
		return @$this->__[$variable];
	}
}