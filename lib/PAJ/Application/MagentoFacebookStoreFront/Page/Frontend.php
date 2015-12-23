<?php
/**
 *  
 *  Copyright (C) 2015 paj@gaiterjones.com
 *
 * 	
 *
 */

namespace PAJ\Application\MagentoFacebookStoreFront\Page;

/**
 * Page FRONTEND class.
 * 
 * @extends Page
 */
class Frontend extends \PAJ\Application\MagentoFacebookStoreFront\Page {


	public function __construct($_variables) {
	
		// load parent
		parent::__construct($_variables);

		// define valid subpages and login status for pages
		//
		$_validSubPages				=array(
											'home'
									);
		
		$_subPageRequiresLogin		=array(false);
		$_subPageLinks=false;
		
		$_subPage=$this->get('requestedsubpage'); // get subpage
		$_loggedIn=$this->get('loggedin'); // get logged in status

		// check requested sub page is valid
		//
		if (!in_array($_subPage, $_validSubPages) && $_subPage != null) {
			throw new \Exception ($this->__t->__('The requested page was not found.'));
		}			
		
		// load html for page - DESKTOP
		\PAJ\Application\MagentoFacebookStoreFront\Page\Frontend\HTML\Template\Desktop::html();
			
		
		// render page
		$this->createPage();
		
	}


		/**
		 * renderPage function.
		 * 
		 * @access private
		 * @return void
		 */
		private function createPage()
		{
			$_HTMLArray=$this->get('html');
			$_errorMessage=$this->get('errorMessage');
			$_subPage=$this->get('requestedsubpage');
			$_pageHtml='';
			
			/* render html from html array */
			foreach ($_HTMLArray as $_obj)
			{
				$_usePageHtml=false;
				
				foreach ($_obj as $_key=>$_value)
				{
					
					if ($_key === 'page')
					{				
						$_array=$_value;
						foreach ($_array as $_key=>$_page)
						{
							// render default html
							if ($_page == '*')	{$_usePageHtml=true;}
						
							if (empty($_errorMessage))
							{
								// no errors
								if ($_subPage === 'home' && $_page == 'home')	{$_usePageHtml=true; }
							
							} else {
							
								// display error html
								
								if ($_page == 'error')	{$_usePageHtml=true; }
								
							}
						
						}
	
					}
					
					if ($_key === 'html')
					{
						if ($_usePageHtml)
						{
							$_pageHtml=$_pageHtml.$_value;
						}
					}
	
				}
	
			}
			
			$this->set('pageHtml',$_pageHtml);
		}



		/**
		 * __toString function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __toString()
		{
			$_html=$this->get('pageHtml');
			
			// log page views to logging module
			if ($this->__config->get('loggingEnabled')) {
				\PAJ\Library\Log\Helper::logThis('PAGE : '. $this->get('requestedsubpage'). ' - '. $_SERVER[REQUEST_URI],$this->__config->get('applicationName'),false);
			}

			// return and minify DEV true/false, ULTRA true/false
			return ($this->minify($_html,true,false));

		}

}
?>