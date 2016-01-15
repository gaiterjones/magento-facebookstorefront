<?php
/*


	Edit configuration settings here

*/

// 
//
//

namespace PAJ\Application\MagentoFacebookStoreFront;

define('ROOT_DIR', dirname(__FILE__));

class config
{
	// CONFIG
	//
	const applicationName = 'Magento Facebook Store Front 2.0';
	const applicationURL = 'http://dev2015.dev.vw-e.de/paj/www/MagentoFacebookStoreFront/';
	const applicationDomain = 'medazzaland.co.uk';
	const siteTitle='A simple, fast, responsive Magento store front application for Facebook.';
	const useLongDescriptions=true;
	const showProductPrice=true;
	
	// facebook application id
	const fbAppID = 'XXX';
	// facebook application secret
	const fbAppSecret = 'XXX';		
	// The URL to the facebook page or tab
	const fbURL = 'https://apps.facebook.com/XXX';
	// The URL to the facebook app canvas url
	const fbAppTabURL = 'https://apps.facebook.com/XXX';
	// The URL to the facebook tab
	const fbAppCanvasURL = '';
	// The URL to the facebook page or tab
	const fbLikeURL = '';	
	// image file to use as an icon for facebook wallposts etc.
	const appIcon = 'icon.png';
	
	// configure memcache
	const useMemcache=false;
	const memcacheServer='localhost';
	const memcacheServerPort='11211';
	const memcacheTTL='604800';
	const cacheKey='MAGENTOFACEBOOKSTOREFRONT';	
	
	// configure session variables
	//
	const sessionEnabled = true;
	const sessionLifetime = 86400;

	// security
	//
	const blockFailedAttempts = true;
	const SSLLogin = false;
	const securityEnabled = false;
	
	// define path to Magento app
	const pathToMagentoApp ='/../../../../app/Mage.php';
	const includeSubCategories = false;
	
	public $_serverURL;
	public $_serverPath;
	
	public function __construct()
	{
		if (php_sapi_name() != 'cli') {
			$this->_serverURL=$this->serverURL();
			$this->_serverPath=$this->serverPath();
		}
	}
	
	
    public function get($constant) {
	
	    $constant = 'self::'. $constant;
	
	    if(defined($constant)) {
	        return constant($constant);
	    }
	    else {
	        return false;
	    }

	}

	/**
	 * serverURL function.
	 * 
	 * @access public
	 * @return string
	 */
	public function serverURL() {
	 $_serverURL = 'http';
	 if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$_serverURL .= "s";}
	 $_serverURL .= "://";
	 if ($_SERVER["SERVER_PORT"] != "80") {
	  $_serverURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
	 } else {
	  $_serverURL .= $_SERVER["SERVER_NAME"];
	 }
	 return $_serverURL;
	}
	
	private function serverPath() {
	 $_serverPath=$_SERVER["REQUEST_URI"];
	 //$_serverPath=explode('?',$_serverPath);
	 //$_serverPath=$_serverPath[0];
	 
	 return $_serverPath;
	}	
	
}




?>