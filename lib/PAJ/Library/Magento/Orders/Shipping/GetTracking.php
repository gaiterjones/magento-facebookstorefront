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
 * Magento Get Tracking
 * -- Class to retrieve traking data from order
 * @access public
 * @return Magento Tracking Data
 */
namespace PAJ\Library\Magento\Orders\Shipping; 
class GetTracking extends \PAJ\Library\Magento\Connect
{

	public function __construct($_variables) {
		
			parent::__construct();
			$this->loadClassVariables($_variables);
			$this->getTracking();
		
	}

	
	public function getTracking()
	{
		
		$this->set('success',false);
		$this->set('output',false);
		$this->set('errormessage','Not defined');
		
		$_orderID=false;
		$_storeID=$this->get('storeid');
		
		$_trackingNumbers=array();
		$_orderID=$this->get('orderid');
		

		$_order = \Mage::getModel('sales/order')->loadByIncrementId($_orderID);
		$_shipmentCollection = \Mage::getResourceModel('sales/order_shipment_collection')
					->setOrderFilter($_order)
					->load();
					
		if ($_shipmentCollection) {
			
			foreach($_shipmentCollection as $_shipment){
				
				foreach($_shipment->getAllTracks() as $_tracking_number){
					$_trackingNumbers[]=$_tracking_number->getNumber();
				}
				
				//echo "Product(s) on shipment:<br/>";
				//foreach ($_shipment->getAllItems() as $_product){
				//	echo $_product->getName() . "<br/>";
				//}
			}
			
				$this->set('success',true);
				$this->set('output',array(
					'getTrackingNumbers' => $_trackingNumbers,
					'getTrackingOrder' => $_orderID,
					'getTrackingNumberCount' => count($_trackingNumbers)
				));		

			
		}
	
	}

}  
?>