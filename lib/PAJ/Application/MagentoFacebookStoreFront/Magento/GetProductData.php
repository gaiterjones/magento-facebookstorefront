<?php
/**
 *  
 *  Copyright (C) 2015 paj@gaiterjones.com
 *
 *
 */

namespace PAJ\Application\MagentoFacebookStoreFront\Magento;

/**
 * magento GetProductPrice class.
 * 
 * @extends -
 */
class GetProductData {


	public function __construct() {

	}

	public function formattedPrice($_product,$_taxRate=false,$store_id = 1)
	{
		$_price = $_product->getFinalPrice();
		//$_price = $_product->getPrice();
		
		if ($_price)
		{
			$_discounted_price = \Mage::getResourceModel('catalogrule/rule')->getRulePrice(
									\Mage::app()->getLocale()->storeTimeStamp($store_id),
									\Mage::app()->getStore($store_id)->getWebsiteId(),
									$_product->getId());

			// if the product isn't discounted then default back to the original price
			if ($_discounted_price===false) {
				
				$_finalPrice=$_price;
				
			} else {
				
				$_finalPrice=$_discounted_price;
				
			}
				
			// add tax
			if ($_taxRate) {
				$_finalPrice=self::ConvertAmount($_finalPrice,$_taxRate);
			}
			
			$_formattedPrice = \Mage::helper('core')->currency(\Mage::helper('tax')->getPrice($_product, $_finalPrice), true, false);
			
			return $_formattedPrice;
		}
		
		return false;
	}
	
	public function formattedGroupedPrice($_product,$_taxRate=false,$_getTierPrices=false,$store_id = 1)
	{
		
		if ($_product)
		{
			// get lowest price from tiers
			 
			$_groupedProductChildPrices=array();
			 
			// get child products of grouped product
			//
			$_childProductIds = $_product->getTypeInstance()->getChildrenIds($_product->getId());
			 
			foreach ($_childProductIds as $_ids) {
			 
				foreach ($_ids as $_id) {
			 
					$_childProduct = \Mage::getModel('catalog/product')->load($_id);
			 
					// get all associated group product pricing, including tier prices
					$_groupedProductChildPrices[] = $_childProduct['price'];
			 
						// include tiers prices in from price
						if ($_getTierPrices)
						{
							if($_childProduct->getTierPrice() != NULL) {
								foreach($_childProduct->getTierPrice() as $tier){
									$_groupedProductChildPrices[]= $tier['price'];
								}
							}
						}
			 
				}
			}
			 
			// set lowest price and reset minimal price variables
			//
			$_lowestGroupProductPrice=min($_groupedProductChildPrices);
				
			// add tax
			if ($_taxRate) {
				$_lowestGroupProductPrice=self::ConvertAmount($_lowestGroupProductPrice,$_taxRate);
			}
			
			$_formattedPrice = \Mage::helper('core')->currency(\Mage::helper('tax')->getPrice($_product, $_lowestGroupProductPrice), true, false);
			
			return $_formattedPrice;
		}
		
		return false;
	}	

	public function getProductLabel($_product=false){
	
		$_otherOffer=false;
		$_productLabel='';
		if ($_product)
		{
				$count_labels = 0;
				
					$productNewFromDate = $_product->getNewsFromDate();
					$productNewToDate = $_product->getNewsToDate();
				
					if(!(empty($productNewToDate))){

						$now = new \DateTime(null, new \DateTimeZone('Europe/London'));	
						$newFromDate = new \DateTime($productNewFromDate,new \DateTimeZone('Europe/London'));
						$newToDate = new \DateTime($productNewToDate,new \DateTimeZone('Europe/London'));
						$productAgeDays=$newFromDate->diff($now);
					
						// show products as new if created in last 30 days	
						if($productAgeDays->format('%a') <= 30){
							
							$count_labels++;
							$_productLabel=$_productLabel. 'New';
						}
					}

					
					// Init time
					//
					$_productSpecialPrice=false;
					$_now = strtotime(date("Y-m-d 00:00:00"));
					
					// check for special price time range
					//			
						if(strtotime($_product->getspecial_from_date()) <= $_now && strtotime($_product->getspecial_to_date()) >= $_now)
						{
							$_productSpecialPrice=true;
						}
						
					if ($_productSpecialPrice){
						$count_labels++;
						$_productLabel=$_productLabel.'Special';
					}
			
		}
		
		if ($count_labels) {return '<div class="ribbon-wrapper-green"><div class="ribbon-green">'. $_productLabel. '</div></div>';}
	}

	/**
	 * ConvertAmount
	 *
	 * @param   float   $amount     Amount to convert
	 * @param   float   $rate       Tax rate (if NULL the rate is automatically selected)
	 * @param   bool    $add_tax    Sets the direction of conversion (if true rate ($ rate) will be added, if false it will be deducted)
	 *
	 * @return float    $amount     Converted amount
	 */
	public function ConvertAmount($amount,$rate=NULL,$add_tax=true){

		if($rate == NULL) {
			
			//$rate = self::getCurrentRate();
			return false;
		}

		$tax_coeff =100 / (100 + $rate);


		if($add_tax){
			$amount = $amount / $tax_coeff;
		}
		else{
			$amount = $amount * $tax_coeff;
		}
		return round($amount,2);
	}
}
