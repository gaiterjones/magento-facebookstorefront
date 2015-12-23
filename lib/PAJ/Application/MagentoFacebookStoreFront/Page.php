<?php
/**
 *  
 *  Copyright (C) 2013
 *
 *
 *  @who	   	PAJ
 *  @info   	paj@gaiterjones.com
 *  @license    -
 * 	
 *
 */
namespace PAJ\Application\MagentoFacebookStoreFront;

class Page {
	
	public $__;
	public $__config;
	public $__t; 	
	
		public function __construct($_variables) {
		
			// load class variables
			foreach ($_variables as $key => $value)
			{
				$this->set($key,$value);
			}

			// load app variables
			$this->__config= new config();
			$this->loadTranslator();
			
		
	
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
	
	    function __destruct() {
	       
	       unset($this->__config);
	       unset($this->__);
	    }
	   
		public function set($key,$value)
		{
		    $this->__[$key] = $value;
		}
		
	  	public function get($variable)
		{
		    return $this->__[$variable];
		}
		
	   // helpers
	   public function loadClassVariables($_variableArray)
	   {
			if(is_array($_variableArray)) {
			
				foreach ($_variableArray as $key => $value)
				{
					$this->set($key,$value);
				}
			} 
			   
	    }
		
		// load cms content
		protected function loadCMSContent($_cmsContent)
		{
			foreach ($_cmsContent as $_element => $_data)
			{
				$this->set('cms_'. $_element,$_data);
			}		
		}
		
		
		public function truncateText($_text,$_length=25)
		{
			$_truncatedText=$_text;
			
			if (strlen($_text) > $_length) { $_truncatedText=preg_replace('/\s+?(\S+)?$/', '', substr($_text, 0, $_length)). '...'; }	
			
			return (htmlentities($_truncatedText, ENT_QUOTES, "UTF-8"));
		
		}
		
		protected function minify($_html,$_dev=false,$_sanitize=true)
		{
			if (!$_dev) {
				// minify live code
				return ($_sanitize ? $this->sanitize(\PAJ\Library\Minify\HTML::minify($_html,array('jsCleanComments' => true))) : \PAJ\Library\Minify\HTML::minify($_html,array('jsCleanComments' => true)));
			
			} else {
			
				return $_html;
			}
		}
		
		protected function sanitize($_html) {
		   
			return (preg_replace('#\R+#', ' ', $_html));
		}				
		
		/**
		 * curPageURL function.
		 * @what returns url of current page
		 * @access private
		 * @return what
		 */
		public function curPageURL() {
		 $pageURL = 'http';
		 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		 $pageURL .= "://";
		 if ($_SERVER["SERVER_PORT"] != "80") {
		  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		 } else {
		  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		 }
		 return $pageURL;
		}

		/**
		 * humanTiming function.
		 * @what returns time lapsed in easy human reading form
		 * @access protected
		 * @return string
		 */		
		protected function humanTiming ($time1,$time2)
		{
		
		    $time = $time1 - $time2; // to get the time since that moment
		
		    $tokens = array (
		        31536000 => 'year',
		        2592000 => 'month',
		        604800 => 'week',
		        86400 => 'day',
		        3600 => 'hour',
		        60 => 'minute',
		        1 => 'second'
		    );
		
	    foreach ($tokens as $unit => $text) {
	        if ($time < $unit) continue;
	        $numberOfUnits = floor($time / $unit);
	        return $numberOfUnits.' '. $this->__t->__($text.(($numberOfUnits>1)?'s':''). ' ago.');
	    }
		
		}

		/**
		 * slugify function.
		 * @what returns SEO friendly text for use as url slug
		 * @access protected
		 * @return string
		 */			
		static public function slugify($text)
		{ 
		  // replace non letter or digits by -
		  $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

		  // trim
		  $text = trim($text, '-');

		  // transliterate
		  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		  // lowercase
		  $text = strtolower($text);

		  // remove unwanted characters
		  $text = preg_replace('~[^-\w]+~', '', $text);

		  if (empty($text))
		  {
			return 'other';
		  }

		  return $text;
		}		


}
?>