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
 * load a product and get the category data
 */
namespace PAJ\Library\Magento\Product;
class Category extends \PAJ\Library\Magento\Connect {



	public function __construct() {

		parent::__construct();
		
	}
	
	public function getCategory($_id,$_debug=false,$_getCategoryName=true,$_storeID='0')
	{
		$_product = \Mage::getModel('catalog/product')->load($_id);
		
		$_categoryIds = $_product->getCategoryIds();

		if(count($_categoryIds) ){
			
			if ($_getCategoryName)
			{	// return the first category name
				$_firstCategoryId = $_categoryIds[0];
				$_category = \Mage::getModel('catalog/category')->setStoreId($_storeID)->load($_firstCategoryId);
				
				return $_category->getName();
			} else {
				// return list of id's
				return implode(',',$_categoryIds);
			}
			
		}
		
		return false;
	}
	
}