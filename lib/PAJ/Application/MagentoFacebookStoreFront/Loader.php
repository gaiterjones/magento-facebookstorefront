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
 *  http://www.medazzaland.co.uk/dropbox/dev/PAJ/www/MagentoFacebookStoreFront/
 *
 */
namespace PAJ\Application\MagentoFacebookStoreFront;

/* Main application Loader */
class Loader
{
	
	protected $__;
	protected $__config;
	protected $__security;
	
	public function __construct() {
		
		try
		{
			$this->set('errorMessage','');
			$this->loadConfig();
			$this->loadSecurity();			
			$this->loadExternalVariables();			
			$this->renderPage();
		
		}
		catch (\Exception $e)
	    {
	    	$this->set('errorMessage', 'ERROR : '. $e->getMessage(). "\n". ' <!-- <br><pre>'. "\n". $this->getExceptionTraceAsString($e). '</pre> -->');
			
			// log to logging module
			if ($this->__config->get('loggingEnabled')) {
				\PAJ\Library\Log\Helper::logThis('EXCEPTION : '. $e->getMessage(),$this->__config->get('applicationName'),true,$this->__config->get('logFilePath').'log',false);
			}			
						
	    	if (php_sapi_name() != 'cli') {
				$this->set('requestedpage','frontend_home');
				$this->renderPage();
			} else {
			
				echo $e->getMessage(). "\n";
				
			}
			
			exit;
	    }
	}

	private function loadConfig()
	{
		$this->__config= new config();
		
		$_version='v2.0.0';
		$_versionNumber=explode('-',$_version);
		$_versionNumber=$_versionNumber[0];
		
		$this->set('version',$_version);
		$this->set('versionNumber',$_versionNumber);
	}
	
	private function loadSecurity()
	{
		if (php_sapi_name() != 'cli') {
			$this->__security= new \PAJ\Library\Security\SecurityController();
			$this->set('loggedin',$this->__security->get('loggedin'));
		}

	}
	
	private function loadExternalVariables()
	{
		
		
		// load language
		//
		if(isset($_GET['lang'])) { 
			$_SESSION['languagecode'] = $_GET['lang'];
		} else { 	
			if(!isset($_SESSION['languagecode'])) {$_SESSION['languagecode'] = $this->getBrowserLanguage();}
		}

		// default gui / sub page
		$_defaultSubPage='home';
		$_defaultGUI='frontend';

		
		// -- initialise variables from GET	
		//
		if(isset($_GET['ajax'])){ $_ajaxRequest = true;} else { $_ajaxRequest = false;}	
		if(isset($_GET['class'])){ $_ajaxClass = $_GET['class'];} else { $_ajaxClass = false;}
		
		if(isset($_GET['page'])){ $_defaultSubPage = $_GET['page'];}
		
		if(isset($_GET['gui'])){
			$_defaultGUI = $_GET['gui'];
			$_defaultSubPage=($this->get('loggedin') ? 'loggedin' : 'notloggedin');
		}
		
		$this->set('requestedpage',$_defaultGUI. '_'. $_defaultSubPage);
		
		// - ? redirect - facebook app canvas url
		// - redirects desktop to facebook tab app
		// - mobiles go to mobile url
		// - need to check for fb scraper otherwise linking fails
		$_redirect=false;
		
		if (in_array($_SERVER['HTTP_USER_AGENT'], array(
		  'facebookexternalhit/1.1 (+https://www.facebook.com/externalhit_uatext.php)',
		  'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)'
		))) {
		  //it's probably Facebook's bot
		}
		else {
		  //that's not Facebook
			if(isset($_GET['redirect'])){ $_redirect=true; }
			
			if ($_redirect)
			{
				echo '<script>top.location="'. $this->__config->get('fbURL'). '"</script>';
				exit;
			}
		}		
		
		// use to provide tab installation link ?fbtab=true
		if(isset($_GET['fbapp']))
		{ 
			echo '<a href="https://www.facebook.com/dialog/pagetab?app_id='. $this->__config->get('fbAppID'). '&display=popup&next='. $this->__config->get('applicationURL'). '">Click here to install Facebook application.</a>';
			exit;
		}

		// -- process ajax requests
		//
		if ($_ajaxRequest)
		{
			if ($_ajaxClass) { // ajax class set

				$_ajaxRequest=new \PAJ\Library\Ajax\Request($_ajaxClass,array(
					'PAJ_Application_MagentoFacebookStoreFront_Magento_GetProducts',
					'PAJ_Application_MagentoFacebookStoreFront_Magento_GetCategories'
				));
				
				
				$_request_headers        = apache_request_headers();
				$_http_origin            = $_request_headers['Origin'];
				
				// allowed domains for this ajax request:
				$_allowed_http_origins   = array(
											'http://blog.gaiterjones.com',
											'http://medazzaland.co.uk',
											'http://localhost'
										  );
										  
				if (in_array($_http_origin, $_allowed_http_origins)){  
					@header("Access-Control-Allow-Origin: " . $_http_origin);
				}		

				//header('Access-Control-Allow-Origin: *');				
				
				header('Content-type: text/json; charset=utf-8');
					echo $_ajaxRequest;
						unset($_ajaxRequest);
							exit;
			}
			// invalid ajax class
			exit;
		}	
		

	}
	

		public function renderPage()
		{
			// ouput methods
			// 1. HTML
			
			// get Page class
			$_pageClass=explode('_',$this->get('requestedpage'));
			$_requestedPage=$_pageClass[0];
			$_requestedSubPage=null;
			
			if (isset($_pageClass[1])) { $_requestedSubPage=$_pageClass[1]; }
			
			$_pageClass=__NAMESPACE__. '\\Page\\'.ucfirst($_requestedPage);
			
			if (!class_exists($_pageClass)) { throw new exception('Requested page class '. $_pageClass. ' is not valid.'); }
			
			$_page = new $_pageClass(array(
			  "requestedpage"		 	=> 		$_requestedPage,
			  "requestedsubpage"	 	=> 		$_requestedSubPage,
			  "version"	 				=> 		$this->get('version'),
			  "versionnumber"			=> 		$this->get('versionNumber'),			  
			  "applicationname"		 	=> 		$this->__config->get('applicationName'),
			  "sitetitle"			 	=> 		$this->__config->get('siteTitle'),
			  "languagecode"			=>		$_SESSION['languagecode'],
			  "errorMessage" 		 	=>		$this->__['errorMessage']
			));			
			
			// output
			
				echo $_page;
			
			unset($_page);
		}

		public function getExceptionTraceAsString($exception) {
			$rtn = "";
			$count = 0;
			foreach ($exception->getTrace() as $frame) {
				$args = "";
				if (isset($frame['args'])) {
					$args = array();
					foreach ($frame['args'] as $arg) {
						if (is_string($arg)) {
							$args[] = "'" . $arg . "'";
						} elseif (is_array($arg)) {
							$args[] = "Array";
						} elseif (is_null($arg)) {
							$args[] = 'NULL';
						} elseif (is_bool($arg)) {
							$args[] = ($arg) ? "true" : "false";
						} elseif (is_object($arg)) {
							$args[] = get_class($arg);
						} elseif (is_resource($arg)) {
							$args[] = get_resource_type($arg);
						} else {
							$args[] = $arg;
						}   
					}   
					$args = join(", ", $args);
				}
				$rtn .= sprintf( "#%s %s(%s): %s(%s)\n",
										 $count,
										 $frame['file'],
										 $frame['line'],
										 $frame['function'],
										 $args );
				$count++;
			}
			return $rtn;
		}			

		public function getBrowserLanguage() {
		
			if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			{
				foreach (explode(",", strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])) as $accept) {
					if (preg_match("!([a-z-]+)(;q=([0-9.]+))?!", trim($accept), $found)) {
						$langs[] = $found[1];
						$quality[] = (isset($found[3]) ? (float) $found[3] : 1.0);
					}
				}
				// Order the language codes
				array_multisort($quality, SORT_NUMERIC, SORT_DESC, $langs);
				
				$_languageCode=explode('-',$langs[0]);
				$_languageCode=$_languageCode[0];
				return strtolower($_languageCode);
				
			} else {
			
				return 'en';
			}
			
		}
		
		public function set($key,$value)
		{
			$this->__[$key] = $value;
		}
			
	  	public function get($variable)
		{
			return $this->__[$variable];
		}
		
	}
