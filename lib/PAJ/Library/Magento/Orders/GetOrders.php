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
namespace PAJ\Library\Magento\Orders; 
class GetOrders extends \PAJ\Library\Magento\Connect
{

	public function __construct($_variables) {
		
			parent::__construct();
			$this->loadClassVariables($_variables);
			$this->getOrders();
		
	}

	
	public function getOrders()
	{
		
		$this->set('success',false);
		$this->set('output',false);
		$this->set('errormessage','Not defined');
		
		$_orderID=false;
		$_timeRange=false;
		$_collection=false;
		$_date=false;
		
		$_storeID=$this->get('storeid');
		
		// options for collection, per order, time range or date
		//
		$_orderID=$this->get('orderid');
		$_timeRange=$this->get('timerange');
		$_date=$this->get('date');
				
		if ($_orderID)
		{
			$this->getMagentoOrderByID($_orderID,$_storeID);
			
		} else if ($_date)	{
			$this->getMagentoOrderByDate(false,$_storeID,$_date);
		} else if ($_timeRange)	{
			$this->getMagentoOrderByDate($_timeRange,$_storeID,false);
		}

		$_collection=$this->get('collection');
		
		if ($_collection) {
		
			$this->set('success',true);
			$this->set('output',$_collection);
		}
	
	}
	
	private function getMagentoOrderByDate($_timeRange=false,$_storeID,$_date=false)
	{

		$_obj=new \PAJ\Library\Magento\Collection\Data();
			$_obj->getOrdersByDate($_timeRange,$_storeID,$_date);
				

		$_collection=$_obj->get('collection');
			unset($_obj);
			
		$this->set('collection',$_collection);
	}
	
	
	private function getMagentoOrderByID($_orderID,$_storeID)
	{
		
		$_obj=new \PAJ\Library\Magento\Collection\Data();
			$_obj->getOrdersByOrderID($_orderID,$_storeID);
				

		$_collection=$_obj->get('collection');
			unset($_obj);
			
		$this->set('collection',$_collection);
	}	

}  
?>