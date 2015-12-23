<?php	
/**
 *  
 *  Copyright (C) 2014
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
 * load a product and troll through attributes defined in array
 */
namespace PAJ\Library\Magento\Product;
class Attributes extends \PAJ\Library\Magento\Connect {



	public function __construct() {

		parent::__construct();
		


	}
	
	public function getAttribute($_id,$_attributeNames,$_debug=false)
	{
		$_product = \Mage::getModel('catalog/product')->load($_id);
		$_attributes = $_product->getAttributes();
			
		$_attributeValue = false;
		$_value=false;
		
		if (is_array($_attributeNames))
		{
			$_attributeValue=array();
			
			foreach ($_attributeNames as $_attributeLabel => $_attributeMageName)
			{
				
				if ($_attributeLabel==='stock') {
					
					$_attributeValue["$_attributeLabel"]=\Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty();
					
				} else {
				
					if(array_key_exists($_attributeMageName,$_attributes)){
						
						$_attributesobj = $_attributes["$_attributeMageName"];
						
						$_value=$_attributesobj->getFrontend()->getValue($_product);
						
						if ($_value==='No') {$_value='xxx';}
						if ($_value==='Nein') {$_value='xxx';}
						
						if ($_value==='') {$_value=false;}
						if (empty($_value)) {$_value=false;}
						if (is_array($_value)) {$_value='Array';}
						
						$_attributeValue["$_attributeLabel"]= $_value;
						
						if ($_debug) {
						
							if ($_id=="1234")
							{
								echo $_id. "->". $_attributeLabel . "->". $_value. "<br>";
							}

						}
						
					}
				}
			}
			
		} else {
		
			if(array_key_exists($_attributeNames,$_attributes)){
				
				$_attributesobj = $_attributes["$_attributeNames"];
				$_value=$_attributesobj->getFrontend()->getValue($_product);
				
				if (empty($_value)) {$_value=false;}
				if ($_value==='No') {$_value=false;}
				
				$_attributeValue["$_attributeLabel"]= $_value;
			}
		
		}

		return $_attributeValue; //attribute value for 'my_attribute_name'
	}
	
}