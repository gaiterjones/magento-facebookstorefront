<?php	
/**
 *  
 *  Copyright (C) 2015
 *
 *
 *  @who	   	PAJ
 *  @info   	paj@gaiterjones.com
 *  @license    blog.gaiterjones.com
 * 	
 *
 */

 /**
 * Magento collection class
 * load a product
 */
namespace PAJ\Library\Magento\Product;
class Load extends \PAJ\Library\Magento\Connect {



	public function __construct() {

		parent::__construct();
		
		
	}
	
	public function loadProduct($_id,$_storeID='0')
	{
		$_product = \Mage::getModel('catalog/product')->setStoreId($_storeID)->load($_id);
		
		if($_product){
			
			return $_product;
			
		}
		
		return false;
	}
	
}