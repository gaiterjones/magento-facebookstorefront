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
namespace PAJ\Library\Magento\Category;
class Load extends \PAJ\Library\Magento\Connect {



	public function __construct() {

		parent::__construct();
		
		
	}
	
	public function loadCategoryTree($_storeID='0')
	{
		$_mageCategory = \Mage::getModel ('catalog/category');
		$_tree = $_mageCategory->getTreeModel();
		$_tree->load();

		$ids = $_tree->getCollection()->getAllIds();

		$categories = array();

		$x = 0;

		if ($ids) {

			$_categoryData=array();
			
			foreach ( $ids as $id ) {

				$_category = \Mage::getModel('catalog/category')->setStoreId($_storeID)->load($id);
				
				$_categoryUrl = $_category->getUrl();
				$_categoryUrlKey=$_category->getUrl_key();
				$_parentCategoryId = $_category->getParentId();
				$_categoryLevel = $_category->getLevel();
				$_categoryPosition = $_category->getPosition();
				$_categoryDescription = $_category->getDescription();
				$_categoryName=$_category->getName();
				$_categoryPath=$_category->getPath();
				$_categoryDisplayMode=$_category->getDisplay_mode();
				
				$_categoryData[]=array(
					'id' => $id,
					'categoryname' => $_categoryName,
					'categoryurlkey' => $_categoryUrlKey,
					'parentcategoryid' => $_parentCategoryId,
					'categorylevel' => $_categoryLevel,
					'categorydisplaymode' => $_categoryDisplayMode,
					'categorypath' => $_categoryPath,
					'categoryposition' => $_categoryPosition,
					'categorydescription' => $_categoryDescription);
				

			}
				
				if($_categoryData){
					
					return $_categoryData;
					
				}
				
				return false;
			}
	}
	
}