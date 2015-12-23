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
 * Magento Orders
 * -- Class to retrieve order data from Magento
 * @access public
 * @return Magento Order
 */
namespace PAJ\Library\Magento\Orders\Shipping; 
class SaveTracking extends \PAJ\Library\Magento\Connect
{

	public function __construct($_variables) {
		
			parent::__construct();
			$this->loadClassVariables($_variables);
			$this->saveTracking();
		
	}
	
	public function saveTracking()
	{
		
		$this->set('success',false);
		$this->set('output',false);
		$this->set('errormessage','Not defined');
		

		$_orderID=$this->get('orderid');
		$_trackingNumber=$this->get('trackingnumber');
		$_shippingTitle=$this->get('shippingtitle');
		
		$_order = \Mage::getModel('sales/order')->loadByIncrementId($_orderID);
		$_shipment = $_order->getShipmentsCollection()->getFirstItem();
		$_shipmentIncrementId = $_shipment->getIncrementId();	
		
		if($_shipmentIncrementId)
		{
			// use to get carriers
			//
			//$carriersData = array();
			//$carriers = \Mage::getsingleton("shipping/config")->getAllCarriers();
			//foreach($carriers as $code => $method){
			//	$carriersData[$code] = array(
			//		"title"     => \Mage::getStoreConfig("carriers/$code/title"),
			//		"methods"   => $method->getMethods(),
			//	 );
			//}

			//print_r($carriersData);
			//exit;
			
			$_shipment = \Mage::getModel('sales/order_shipment')->loadByIncrementId($_shipmentIncrementId);

					 /* @var $_shipment Mage_Sales_Model_Order_Shipment */
					$_track = \Mage::getModel('sales/order_shipment_track')
								 ->setNumber($_trackingNumber)
								 ->setCarrierCode('matrixrate')
								 ->setTitle($_shippingTitle);

					 $_shipment->addTrack($_track);

					 try {
						 
						 $_shipment->save();
						 $_order->addStatusHistoryComment($_shippingTitle. ' DHL Tracking Number '. $_trackingNumber)
							->setIsVisibleOnFront(false)
							->setIsCustomerNotified(false);
						 $_order->save();
						 
					 } catch (Mage_Core_Exception $e) {
						 $this->set('errormessage',$e);
					 }		
		 
					$this->set('success',true);
					$this->set('output',array(
						'magentoTrackID' => $_track->getId()
					));
		} else {
				
			$this->set('errormessage','No shipment found');
		}
	}
}  
?>