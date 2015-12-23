<?php
/**
 *  
 *  Copyright (C) 2015 paj@gaiterjones.com
 *
 * 	
 *
 */

namespace PAJ\Library\Language;

class Translator {

    protected $__;
	protected $__cache;
    protected $lang = array();

    public function __construct($_languageCode=false) {
    
    	
		if ($_languageCode)
		{
			$this->set('languageCode',$_languageCode);
		} else {
			// load language
			//
			if(isset($_GET['lang'])) { 
				$_SESSION['languagecode'] = $_GET['lang'];
			} else { 
			
				$_languageCode=$this->getBrowserLanguage();
				
				if(!isset($_SESSION['languagecode'])) {$_SESSION['languagecode'] = $_languageCode;}
			}

			
		}
    }
    
    public function __($_str) {

		$_lang=$this->get('languageCode');		
	
		if ($_lang==='en') { return $_str; } // do not translate default language - english
		
    	try {
			
			$_localeFile= dirname(__FILE__). '/locale/'. $_lang. '.txt';
			
			// validate locale file
			//
			if (!file_exists($_localeFile)) { return $_str; }
			
			// load cached translations
			//
			$this->loadMemcache();
			
			$_cacheConnected=$this->__cache->get('memcacheconnected');
			$_cacheKey='TRANSLATIONS_'.  $_lang. '_'. filemtime($_localeFile);
			
			$this->loadCachedTranslations($_cacheKey);
    	
	        if (!array_key_exists($_lang, $this->lang)) { // translation data array doesn't exist, create it
			
	            if (file_exists($_localeFile)) {
				
	                $_strings = array_map(array($this,'splitStrings'),file($_localeFile));
					
	                foreach ($_strings as $k => $v) {
	                    $this->lang[$_lang][$v[0]] = $v[1];
	                }
					
					// try and memcache translations
					if ($_cacheConnected) { $this->__cache->cacheSet($_cacheKey, serialize($this->lang),86400); }
					
	                return $this->findString($_str, $_lang);
					
	            } else { // no locale file
	                
					return $_str;
	            }
	        
			} else { 
				
				return $this->findString($_str, $_lang);
	        }
	        
	    } catch (\Exception $e) {

		    // catch translation errors quietly, just
		    // return the original string and pretend
		    // nothing happened...
		    return $_str;
		}
    }

    private function findString($_str,$_lang) {
	
        if (array_key_exists($_str, $this->lang[$_lang])) {
            return $this->lang[$_lang][$_str];
        }
		    
		$helper=false;
		if ($helper) { file_put_contents('/home/www/medazzaland/cache/translation_helper.txt', $_str. "\n", FILE_APPEND);} // helper logs translations not found
        
		return $_str;
    }
    
    private function splitStrings($_str) {
        return explode('=',trim($_str));
    }

	private function loadMemcache()
	{
		$this->__cache=new \PAJ\Library\Cache\Memcache();
	}

	private function loadCachedTranslations($_cacheKey)
	{
		$_cacheConnected=$this->__cache->get('memcacheconnected');
		
		if ($_cacheConnected) {

			$_translations=$this->__cache->cacheGet($_cacheKey);

				if ($_translations) { $this->lang=unserialize($_translations);}
		}

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