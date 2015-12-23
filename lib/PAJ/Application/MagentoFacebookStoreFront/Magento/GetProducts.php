<?php
/**
 *  
 *  Copyright (C) 2015 paj@gaiterjones.com
 *
 * 	?ajax&class=PAJ_Application_MagentoFacebookStoreFront_Magento_GetProducts&variables=blah=blah
 *
 */

namespace PAJ\Application\MagentoFacebookStoreFront\Magento;

/**
 * magento GETPRODUCTS class.
 * 
 * @extends Magento LOADER
 */
class GetProducts extends Loader {


	public function __construct($_variables) {

		$this->loadClassVariables($_variables);
		
		parent::__construct();
		
		$this->getMagentoProducts();
	}

	private function getMagentoProducts()
	{
		// init
		$this->set('success',false);
		$this->set('errormessage','No results found.');
		
		$_cached=false;
		$_output=false;
		
		$_languageCode=$this->get('languagecode');
		
		$_page=$this->get('collectionpage');
		$_count=$this->get('collectioncount');
		$_type=$this->get('collectiontype');
		
		if ($this->__config->get('useMemcache'))
		{
			$_key=$this->__config->get('cacheKey'). '-PRODUCTS-'. md5($_languageCode.$_page.$_count.$_type);
			$_output=\PAJ\Library\Cache\Helper::getCachedString($_key,true);
		}
		
		if (!$_output)
		{	
			$this->loadMagento($_languageCode);
			
			$_storeID=$this->get('storeid');
			
			$this->getProductCollection($_storeID,$_page,$_count,$_type);
		
			$_products=$this->get('collection');
		
			if ($_products)
			{
				
				
				// render html
				$_html=HTML::Products($_products,$this->get('baseurlmedia'));
				
				$this->set('success',true);
				
				$_output=array(
					'getMagentoProducts' => array(
						'html' => $this->minify($_html,false,true),
						'collectionappend' => $this->get('collectionappend'),
						'collectionpage' => $_page,
						'productcount' => count($_products),
						'collectionlastitemid' => $this->get('collectionlastitemid'),
						'collectionlastpagenumber' => $this->get('collectionlastpagenumber'),
						'collectionsize' => $this->get('collectionsize')
					)
				);
				
				$this->set('output',$_output +  array('cached' => $_cached));
				
				if ($this->__config->get('useMemcache')) {
					\PAJ\Library\Cache\Helper::setCachedString($_output,$_key,3600);
				}
				
			} else {
			
				$this->set('errormessage','No results found.');

			}

		} else {
			
			$_cached=true;
			$this->set('success',true);
			$this->set('output',$_output +  array('cached' => $_cached));
		}
	
	}
	
	private function getProductCollection($_storeID=0,$_page=1,$_count=18,$_type='ALLPRODUCTS')
	{
		$_obj=new \PAJ\Library\Magento\Collection\Data();
		
		$_type=explode('-',$_type);
		
	
		if ('CATEGORY'===$_type[0]){

			$_obj->getCategoryProducts($_storeID,$_type[1],$_page,$_count);
			
		} else {
			$_obj->getAllProducts($_storeID,$_page,$_count);
		}

		
		$this->set('collection',$_obj->get('collection'));
		$this->set('collectionsize',$_obj->get('collectionsize'));
		$this->set('collectionlastitemid',$_obj->get('collectionlastitemid'));
		$this->set('collectionlastpagenumber',$_obj->get('collectionlastpagenumber'));
		
		$this->set('baseurlmedia',$_obj->get('baseurlmedia'));

		unset ($_obj);
		
	}

	/**
	*  Get current tax rate
	*
	* @return  float   $rate
	*/
	public function getCurrentRate(){
		
		$_calc = \Mage::getSingleton('tax/calculation');
		$_rates = $_calc->getRatesForAllProductTaxClasses($_calc->getRateRequest());
		
		foreach ($_rates as $_class=>$_rate) {
		   $_result = $_rate;
		}
		
		return floatval($_result);
	}	
	
}
?>