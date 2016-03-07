<?php
/**
 *  
 *  Copyright (C) 2016 paj@gaiterjones.com
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
		// define valid subpages and login status for pages
		//
		$_validSubPages=array(
							'home' 		=> array('secure' => false)
		);
		
		$_subPageRequiresLogin		=array(false);
		$_subPageLinks=false;
		
		$_subPage=$this->get('requestedsubpage'); // get subpage
		$_loggedIn=$this->get('loggedin'); // get logged in status

		// check requested sub page is valid
		//
		if (!array_key_exists($_subPage, $_validSubPages) && $_subPage != null) {
			throw new \Exception ($this->__t->__('The requested page was not found.').  '('. $_subPage. ')');
		}		
		
		// load html for page - DESKTOP
		\PAJ\Application\MagentoFacebookStoreFront\Page\Frontend\HTML\Template\Desktop::html();
			
		
		// render page
		$this->createPage($_validSubPages);
		
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
			
			// return and minify DEV true/false, ULTRA true/false
			return ($this->minify($_html,true,false));

		}

}
?>