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
         'name'          => (string)$v[title],        // name
        'sku'           => (string)$v[manufacturer_item_id],        // sku
        'special_price' => ((real)$v[recommended_retail_price]),    // special price        
        'price'         => ((real)$v[recommended_retail_price]),    // price
        'attribute_set' => 'Fahrrad',            // attribute_set
        'type'          => 'configurable',
        'store'         => 'admin',            
        'description'   => (string)$v[title],        // full description
        'short_description' => (string)$v[title],    // short description
        'qty'           => (string) 10,        // qty
        'category_ids'    => $v[category],                // ID of categories
        'weight'        => (string) '',        // weight
        'tax_class_id'  => '2',                    // tax class id (check your ids)
        'manufacturer'    => (string) $v[manufacturer_name],        // manufacturer
        'meta_title' =>   (string) $v[title],            // meta title
        'meta_description' => (string)$v[title],    // meta description
        'meta_keyword' => (string)$v[title]      // meta keywords
 */

 /**
 * Magento collection class
 * save product data
 */
namespace PAJ\Library\Magento\Product;
class Save extends \PAJ\Library\Magento\Connect {



	public function __construct() {

		parent::__construct();
		
		
	}
	
	public function saveProduct($_id,$_data=false)
	{
		$_product = \Mage::getModel('catalog/product')->setStoreId('0')->load($_id);
		
		$_validData=false;
		
		if($_product && $_data){
			
			// MAGMI
			require_once("/home/www/ecommerce/shop01/webapps/magmi/inc/magmi_defs.php");
			//Datapump include
			require_once("magmi_datapump.php");		

			// create a Product import Datapump using Magmi_DatapumpFactory
			$dp=\Magmi_DataPumpFactory::getDataPumpInstance("productimport");
			
			// Begin import session with a profile & running mode, here profile is "default" & running mode is "create".
			// Available modes: "create" creates and updates items, "update" updates only, "xcreate creates only.
			// Important: for values other than "default" profile has to be an existing magmi profile 
			$dp->beginImportSession("default","update");
			
			$_magmiData=array("store"=>"admin","sku"=>$_product->getSku());
			
			foreach ($_data as $_name => $_value) 
			{
				if ($_name=='setcategoryids') {
					// push new data onto mamgmi import
					$_magmiData+= array("category_ids"=>$_value);
					$_validData=true;
					continue;
				}
				
				// push new data onto mamgmi import
				$_magmiData+= array($_name=>str_replace('__and__', '&',$_value));
				$_validData=true;
			}
			
			if ($_validData)
			{
				$dp->ingest($_magmiData);
				$dp->endImportSession();
				
				return array('magmi' => true, 'attributename' => $_name, 'sku' => $_product->getSku(), 'name' => $_product->getName());
			}
			
		}
		
		return false;
	}
	
}