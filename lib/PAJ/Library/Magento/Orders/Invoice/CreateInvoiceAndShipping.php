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
 
/**
 * Magento invoice, ship, track
 * -- Class to invoice, ship and add tracking data to an order
 * @access 
 * @return 
 */
namespace PAJ\Library\Magento\Orders\Invoice; 
class CreateInvoiceAndShipping extends \PAJ\Library\Magento\Connect
{

	public function __construct($_variables) {
		
			parent::__construct();
			$this->loadClassVariables($_variables);
			$this->createInvoice();
		
	}
	
	public function createInvoice()
	{
		
		$this->set('success',false);
		$this->set('output',false);
		$this->set('errormessage','Not defined');
		
		$_debug='';
		$_orderID=$this->get('orderid');
		$_trackingNumber=$this->get('trackingnumber');
		$_shippingTitle=$this->get('shippingtitle');
		$_notifyCustomer=$this->booString($this->get('notifycustomer'));

		try { // and invoice / ship		
		
			$_order = \Mage::getModel('sales/order')->loadByIncrementId($_orderID);
		
			// not invoiced
			if($_order->canInvoice())
			{
				$_debug=$_debug. "Creating invoice... ". "\n";
				
				$_invoice = \Mage::getModel('sales/service_order', $_order)->prepareInvoice();
	 
					if (!$_invoice->getTotalQty()) {
						throw new \Exception('Cannot create an invoice without products.');
					}
					 
					//$_invoice->setRequestedCaptureCase(\Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
					
					$_invoice->register();
					$_transactionSave = \Mage::getModel('core/resource_transaction')
					->addObject($_invoice)
					->addObject($_invoice->getOrder())
					->save();
					 
					$_order->addStatusHistoryComment('Invoice created.')
						->setIsVisibleOnFront(false)
						->setIsCustomerNotified(false);
			}
			
			$_shipment = $_order->getShipmentsCollection()->getFirstItem();
			$_shipmentIncrementId = $_shipment->getIncrementId();	
			
			if(!$_shipmentIncrementId)
			{
				// create new shipment with tracking
				//
				
				$_order = \Mage::getModel('sales/order')->loadByIncrementId($_orderID);
				
				
				if($_order->canShip())
				{
					$_debug=$_debug. "Creating new shipment... ". "\n";
					
					$_itemQty =  $_order->getItemsCollection()->count();
					$_shipment = \Mage::getModel('sales/service_order', $_order)->prepareShipment($this->_getItemQtys($_order));
					$_shipment = new \Mage_Sales_Model_Order_Shipment_Api();
					$_shipmentId = $_shipment->create($_orderID);
				
					$_shipment = $_order->getShipmentsCollection()->getFirstItem();
					$_track = \Mage::getModel('sales/order_shipment_track')
								 ->setNumber($_trackingNumber)
								 ->setCarrierCode('matrixrate')
								 ->setTitle($_shippingTitle);

					 $_shipment->addTrack($_track);
					 
					 if($_notifyCustomer) {$_shipment->sendEmail();}
					 
					 $_shipment->save();
					 
					 $_order->addStatusHistoryComment($_shippingTitle. ' - tracking number '. $_trackingNumber)
						->setIsVisibleOnFront(false)
						->setIsCustomerNotified(false);
					 			
				}		
				

			} else {
				
				// shipment exists
				// add tracking
				
				$_debug=$_debug. "adding tracking data to shipment... ". "\n";
				
				$_shipment = \Mage::getModel('sales/order_shipment')->loadByIncrementId($_shipmentIncrementId);

						 /* @var $_shipment Mage_Sales_Model_Order_Shipment */
						$_track = \Mage::getModel('sales/order_shipment_track')
									 ->setNumber($_trackingNumber)
									 ->setCarrierCode('matrixrate')
									 ->setTitle($_shippingTitle);

						 $_shipment->addTrack($_track);
						 $_shipment->save();
						 if($_notifyCustomer) {$_shipment->sendEmail();}
						 $_order->addStatusHistoryComment($_shippingTitle. ' tracking number '. $_trackingNumber)
							->setIsVisibleOnFront(false)
							->setIsCustomerNotified(false);
		
				//throw new \Exception('No shipment found.');
				//$this->set('errormessage','No shipment found');
			}
			
			$this->_saveOrder($_order);
		
		} catch (\Exception $e) {		
		
			$this->set('errormessage',$e);
			return;
		}	
		
			$this->set('success',true);
			$this->set('output',array(
				'createInvoice' => array(
					'orderid' => $_orderID,
					'notifycustomer' => $_notifyCustomer,
					'debug' => $_debug
				)
			));			
	}
	
	/**
	 * Saves the Order, to complete the full life-cycle of the Order
	 * Order status will now show as Complete
	 *
	 * @param $order Mage_Sales_Model_Order
	 */
	protected function _saveOrder(\Mage_Sales_Model_Order $order)
	{
		$order->setData('state', \Mage_Sales_Model_Order::STATE_COMPLETE);
		$order->setData('status', \Mage_Sales_Model_Order::STATE_COMPLETE);
	 
		$order->save();
	 
		return $this;
	}
	
	/**
	* Get the Quantities shipped for the Order, based on an item-level
	* This method can also be modified, to have the Partial Shipment functionality in place
	*
	* @param $order Mage_Sales_Model_Order
	* @return array
	*/
	protected function _getItemQtys(\Mage_Sales_Model_Order $order)
	{
	
		$qty = array();
		
		foreach ($order->getAllItems() as $_eachItem) {
			
			if ($_eachItem->getParentItemId()) {
				$qty[$_eachItem->getParentItemId()] = $_eachItem->getQtyOrdered();
			} else {
				$qty[$_eachItem->getId()] = $_eachItem->getQtyOrdered();
			}
		}
		
		return $qty;
	}

	protected function booString($_str)
	{
		if ($_str === 'true') { return true; }
		
		return false;
	}
}  
?>