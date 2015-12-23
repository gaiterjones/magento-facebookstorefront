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
namespace PAJ\Library\Magento\Customer; 
class GetCustomerSuffix extends \PAJ\Library\Magento\Connect
{

	public function __construct($_variables) {
		
			parent::__construct();
			$this->loadClassVariables($_variables);
			$this->getSuffix();
		
	}

	
	public function getSuffix()
	{
		
		$this->set('success',false);
		$this->set('output',false);
		$this->set('errormessage','Not defined');
		
		$_customerID=false;

		// options for collection, per order, time range or date
		//
		$_customerID=$this->get('customerid');

				
		if ($_customerID)
		{
			$_customerData = \Mage::getModel('customer/customer')->load($_customerID);
			
			if ($_customerData) {
			
				$this->set('success',true);
				$this->set('output',array('getSuffix' => $_customerData->getSuffix()));
			}
		}


	}
}  
?>