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
 * a generic Magento class for collecting data
 */
namespace PAJ\Library\Magento\Collection;
class Data extends \PAJ\Library\Magento\Connect {



	public function __construct() {

		parent::__construct();
		

	}
	
	public function getCategories($_storeId=0,$_includeChildren=true) {
	
		if ($_includeChildren)
		{
			// get category collection
			$_collection= \Mage::getModel('catalog/category')
				->getCollection() 
				->setStoreId($_storeId)
				->addAttributeToSelect('name') 
				->addAttributeToSelect('is_active')
				->addAttributeToFilter('is_active', 1);
		} else {
			// get category collection
			$_collection= \Mage::getModel('catalog/category')
				->getCollection() 
				->setStoreId($_storeId)
				->addAttributeToSelect('name') 
				->addAttributeToSelect('is_active')
				->addAttributeToFilter('is_active', 1)
				->addAttributeToFilter('level', 2); //2 is first level
		}
			
		$_categoryProductCount=array();	
		
		// determine categories that contain products
		foreach ($_collection as $_category)
		{
			if($_category->getName() != '')
			{
				$_productCollection = \Mage::getModel('catalog/category')->load($_category->getId())
				 ->getProductCollection()
				 ->addAttributeToFilter('status', 1)
				 ->addAttributeToFilter('visibility', 4);
				 
				$_categoryProductCount[$_category->getId()]= $_productCollection->count();
			}
		}
		
		$this->set('categories',$_collection); 
		$this->set('categoriesproductcount',$_categoryProductCount); 
	}
	
	public function getCategory($_id,$_storeId=0) {
	
		// load single category
		//
		$_category = \Mage::getModel('catalog/category')->setStoreId($_storeId)->load($_id);
		
		$this->set('category',$_category); 
	}	
	
	
	public function getNewProducts($storeId='1',$_page=1,$_count=18)
	{  
 
			// load collection
			$todayDate  = \Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

			$_collection = \Mage::getModel('catalog/product')
                    ->getCollection()   
                    ->setStoreId($storeId)
	                 ->addStoreFilter($storeId)  
					 ->addAttributeToFilter('status', 1)
					 ->addAttributeToFilter('visibility', 4)
					 ->addAttributeToSelect('sku')
					 ->addAttributeToSelect('name')
					 ->addAttributeToSelect('description')
					 ->addAttributeToSelect('short_description')
					 ->addAttributeToSelect('url')
					 ->addAttributeToSelect('image')
					 ->addAttributeToSelect('price')             
                     ->addAttributeToFilter('news_from_date', array('date' => true, 'to' => $todayDate))
                     ->addAttributeToFilter('news_to_date', array('or'=> array(
                        0 => array('date' => true, 'from' => $todayDate),
                        1 => array('is' => new Zend_Db_Expr('null')))
                     ), 'left')
                     ->addAttributeToSort('news_from_date', 'desc')
                     ->addAttributeToSort('created_at', 'desc')
                     ->setPage($_page,$_count);   

        
            $this->set('collection',$_collection); 
	}
	
	public function getCategoryProducts($storeId='1',$_category=1,$_page=1,$_count=18)
	{

		$_collection = \Mage::getModel('catalog/category')->load($_category)
		     ->getProductCollection()
             ->setStoreId($storeId)
	         ->addStoreFilter($storeId) 
			 ->addAttributeToFilter('status', 1)
			 ->addAttributeToFilter('visibility', 4)
			 ->addAttributeToSelect('sku')
			 ->addAttributeToSelect('name')
			 ->addAttributeToSelect('description')
			 ->addAttributeToSelect('short_description')
			 ->addAttributeToSelect('url')
			 ->addAttributeToSelect('small_image')
			 ->addAttributeToSelect('image')
			 ->addAttributeToSelect('price')
			 ->addAttributeToSelect('special_from_date')
			 ->addAttributeToSelect('special_to_date')
			 ->addAttributeToSelect('final_price')
			 ->addAttributeToSelect('special_price')			 
		    //->setOrder('price', 'ASC');
		    ->addAttributeToSort('entity_id', 'DESC')
            ->setPage($_page,$_count);
     
		\Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($_collection);
		
		$this->set('collection',$_collection);
		$this->set('collectionlastitemid',$_collection->getLastItem()->getId()); 
		$this->set('collectionlastpagenumber',$_collection->getLastPageNumber()); 
		$this->set('collectionsize',$_collection->getSize()); 
	}


	public function getBestsellingProducts($storeId='1')
	
	//Get Bestselling products for last 30 days
	
	{

    // number of products to display
    $productCount = 18;
 
    // get today and last 30 days time
    $today = time();
    $last = $today - (60*60*24*30);
 
    $from = date("Y-m-d", $last);
    $to = date("Y-m-d", $today);
     
    // get most viewed products for current category
    $products = \Mage::getResourceModel('reports/product_collection')
                    ->addAttributeToSelect('*')     
                    ->addOrderedQty($from, $to)
                    ->setStoreId($storeId)
                    ->addStoreFilter($storeId)                  
                    ->setOrder('ordered_qty', 'desc')
                    ->setPageSize($productCount);
     
    \Mage::getSingleton('catalog/product_status')
            ->addVisibleFilterToCollection($products);
    \Mage::getSingleton('catalog/product_visibility')
            ->addVisibleInCatalogFilterToCollection($products);
     
     $this->set('collection',$products); 
	}

	public function getAllProducts($_storeID=0,$_page=1,$_count=18)
	{  
 
		
		$_collection = \Mage::getModel('catalog/product')
                         ->getCollection()
                         ->setStoreId($_storeID)
	                     ->addStoreFilter($_storeID)  
						 ->addAttributeToFilter('status', 1)
						 ->addAttributeToFilter('visibility', 4)
						 ->addAttributeToSelect('sku')
						 ->addAttributeToSelect('name')
						 ->addAttributeToSelect('price')
						 ->addAttributeToSelect('final_price')
						 ->addAttributeToSelect('image')
						 ->addAttributeToSelect('imageurl')
						 ->addAttributeToSelect('small_image')
						 ->addAttributeToSelect('description')
						 ->addAttributeToSelect('short_description')
						 ->addAttributeToSelect('special_from_date')
						 ->addAttributeToSelect('special_to_date')
						 ->addAttributeToSelect('special_price')
						 ->addAttributeToSelect('url')
                         ->addAttributeToSort('entity_id', 'ASC')
                         ->setPage($_page,$_count);

		\Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($_collection);
		
		$this->set('collection',$_collection);
		$this->set('collectionlastitemid',$_collection->getLastItem()->getId()); 
		$this->set('collectionlastpagenumber',$_collection->getLastPageNumber()); 
		$this->set('collectionsize',$_collection->getSize()); 

	}
	
	public function getAllSKUs($_storeID=0)
	{  
 
		
		$_collection = \Mage::getModel('catalog/product')
                         ->getCollection()
                         ->setStoreId($_storeID)
	                     ->addStoreFilter($_storeID)  
						 ->addAttributeToFilter('status', 1)
						 ->addAttributeToFilter('visibility', 4)
						 ->addAttributeToSelect('sku')
						 ->addAttributeToSelect('name')
						 ->addAttributeToSelect('price')
						 ->addAttributeToSelect('image')
						 ->addAttributeToSelect('imageurl')
						 ->addAttributeToSelect('small_image')
						 ->addAttributeToSelect('description')
						 ->addAttributeToSelect('short_description')
						 ->addAttributeToSelect('url')
                         ->addAttributeToSort('entity_id', 'ASC');
		
		$this->set('collection',$_collection); 
		$this->set('collectionlastitemid',$_collection->getLastItem()->getId()); 
		$this->set('collectionsize',$_collection->getSize()); 		

	}

	public function getProductsForGMerchant($_storeID=0)
	{
		
		/**
		 * define attributes to select from product catalog
		 * 
		 */
		$_collection = \Mage::getModel('catalog/product')
			->getCollection()
            ->setStoreId($_storeID)
	        ->addStoreFilter($_storeID)  		
			->addAttributeToFilter('status', 1)
			->addAttributeToFilter('visibility', 4)
			->addAttributeToSelect('sku')
			->addAttributeToSelect('name')
			->addAttributeToSelect('description')
			->addAttributeToSelect('short_description')
			->addAttributeToSelect('url')
			->addAttributeToSelect('image')
			->addAttributeToSelect('price')
			->addAttributeToSelect('special_price')
			->addAttributeToSelect('manufacturer')
			->addAttributeToSelect('category_ids');
		
		$this->set('collection',$_collection); 
	}	
	
	public function getExternalProducts($_storeID=0)
	{  
 
		$_collection = \Mage::getModel('catalog/product')
                         ->getCollection()
                         ->setStoreId($_storeID)
	                     ->addStoreFilter($_storeID)  
						 ->addAttributeToFilter('status', 1)
						 ->addAttributeToFilter('ext_enable', 1)
						 ->addAttributeToSelect('sku')
						 ->addAttributeToSelect('price')
						 ->addAttributeToSelect('ext_title')
						 ->addAttributeToSelect('name')
						 ->addAttributeToSelect('ext_description')
						 ->addAttributeToSelect('ext_ebayprice')
						 ->addAttributeToSelect('ext_amazonprice')
						 ->addAttributeToSelect('ext_ean')
                         ->addAttributeToSort('sku', 'ASC');
	
		$this->set('collection',$_collection); 

	}
	
	public function getProductBySku($_storeID=0,$_sku)
	{  
 
		$_collection = \Mage::getModel('catalog/product')
                         ->getCollection()
                         ->setStoreId($_storeID)
	                     ->addStoreFilter($_storeID)  
						 ->addAttributeToFilter('status', 1)
						 ->addAttributeToFilter('ext_enable', 1)
						 ->addAttributeToFilter('sku', array('like'=> '%'. $_sku. '%'))
						 ->addAttributeToSelect('sku')
						 ->addAttributeToSelect('price')
						 ->addAttributeToSelect('ext_title')
						 ->addAttributeToSelect('name')
						 ->addAttributeToSelect('ext_description')
						 ->addAttributeToSelect('ext_ebayprice')
						 ->addAttributeToSelect('ext_amazonprice')
						 ->addAttributeToSelect('ext_ean')
                         ->addAttributeToSort('sku', 'ASC');
	
		$this->set('collection',$_collection); 

	}

	public function getProductByAttribute($_storeID=0,$_attributeName,$_attributeValue)
	{  
 					 
		$_collection = \Mage::getModel('catalog/product')
                         ->getCollection()
                         ->setStoreId($_storeID)
	                     ->addStoreFilter($_storeID)  
						 ->addAttributeToFilter($_attributeName, array('like'=> '%'. $_attributeValue. '%'))
						 ->addAttributeToSelect('sku')
						 ->addAttributeToSelect('name')
						 ->addAttributeToSelect('price')
						 ->addAttributeToSelect('image')
						 ->addAttributeToSelect('imageurl')
						 ->addAttributeToSelect('description')
						 ->addAttributeToSelect('url')
                         ->addAttributeToSort('entity_id', 'ASC');
		
		$this->set('collection',$_collection); 						 
	
	}		

	public function getChildProducts($_parentId,$_storeID=0,$_type='grouped')
	{  
 
	$product = \Mage::getModel('catalog/product')->load($_parentId);
	
	if ($_type==='configurable') {

		$childIds = \Mage::getModel('catalog/product_type_configurable')->getChildrenIds($product->getId());
	} else {
		$childIds = \Mage::getModel('catalog/product_type_grouped')->getChildrenIds($product->getId());
	}

    $i = 1;

    foreach ($childIds as $key => $val){

        foreach($val as $keyy => $vall){
            $arr[$i] = $vall;
            $i++;
        }

    }
	
		$_collection = \Mage::getModel('catalog/product')
			->getCollection()
            ->setStoreId($_storeID)
	        ->addStoreFilter($_storeID)			
			->addAttributeToSelect('name')
			->addAttributeToSelect('image')
			->addAttributeToSelect('price')
			->addFieldToFilter('entity_id',array('in' =>array($arr)));

	
		$this->set('collection',$_collection); 

	}	


	public function getOverallBestsellingProducts()
	{  
	// Get overall Bestselling products
	    // number of products to display
	    $productCount = 5;
	     
	    // store ID
	    $storeId    = \Mage::app()->getStore()->getId();      
	     
	    // get most viewed products for current category
	    $products = \Mage::getResourceModel('reports/product_collection')
	                    ->addAttributeToSelect('*')     
	                    ->addOrderedQty()
	                    ->setStoreId($storeId)
	                    ->addStoreFilter($storeId)                  
	                    ->setOrder('ordered_qty', 'desc')
	                    ->setPageSize($productCount);
	     
	    \Mage::getSingleton('catalog/product_status')
	            ->addVisibleFilterToCollection($products);
	    \Mage::getSingleton('catalog/product_visibility')
	            ->addVisibleInCatalogFilterToCollection($products);
	     
	    $this->set('collection',$_collection); 
	}
	
	 public function getCustomersByDate($_days="31")
	{
	
		$_timeRange = date('Y-m-d', strtotime("-". $_days. " day"));
		
		$_customers = \Mage::getModel('customer/customer')->getCollection()
			->addAttributeToFilter('created_at', array('from'  => $_timeRange))
			//->addAttributeToFilter('status', array('neq' => Mage_Sales_Model_Order::STATE_COMPLETE))
			->addAttributeToSelect('*') 
			->addAttributeToSort('created_at', 'DESC');
		
		$this->set('collection',$_customers); 
	
	}

	public function getCustomerByVATID($_storeID=0)
	{
	
		$_collection = \Mage::getModel('customer/customer')
				->getCollection()
				->addAttributeToSelect('firstname')
				->addAttributeToSelect('lastname')
				->addAttributeToSelect('email')
				->addAttributeToSelect('taxvat');

		$_result = array();
		foreach ($_collection as $_customer) {
			$_result[] = $_customer->toArray();
		}
		
		$this->set('collection',$_result);
	
	}

	public function getCustomerSales($_customerId,$fromDate,$toDate)
	{

		
		$fromDate = date('Y-m-d H:i:s', strtotime($fromDate));
		$toDate = date('Y-m-d H:i:s', strtotime($toDate));
		
		$orderCollection = \Mage::getModel('sales/order')->getCollection()
			->addFilter('customer_id', $_customerId)
			->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
			->setOrder('created_at', \Varien_Data_Collection_Db::SORT_ORDER_DESC)
		;
		$numberOfOrders = $orderCollection->count();
		$newestOrder = $orderCollection->getFirstItem();

		$orderCollection->clear()->getSelect()
			->columns(array('total_sales'=>'SUM(main_table.base_grand_total)'))
			->group('customer_id')
		;
		$totalSales = $orderCollection->getFirstItem()
			->getData('total_sales');		
		
		return array('customersales' => $totalSales);
		
	}
	
	public function getCustomerByEmailAddress($_emailAdress,$_storeID=0)
	{
	
		$_customer = \Mage::getModel("customer/customer");
		$_customer->setWebsiteId(\Mage::app()->getWebsite()->getId());
		$_customer->loadByEmail($_emailAdress); //load customer by email id
		
		// get customer data
		$this->set('collection',$_customer);

		$_customer = \Mage::getModel('customer/customer')->load($_customer->getId()); 
		$_shippingaddress = \Mage::getModel('customer/address')->load($_customer->default_shipping); // get default shipping address for customer
		$_addressdata = $_shippingaddress ->getData();
		
		$this->set('addressdata',$_addressdata);
	
	}		

	 public function getOrdersByDate($_days=31,$_storeID,$_date=false)
	{
		if ($_date)
		{
			$_dateStart = date('Y-m-d' . ' 00:00:00', strtotime($_date));
			$_dateEnd = date('Y-m-d' . ' 23:59:59', strtotime($_date));

		} else {
			$_now = \Mage::getModel('core/date')->timestamp(time());
			$_dateStart = date('Y-m-d', strtotime("-". $_days. " day"));
			$_dateEnd = date('Y-m-d' . ' 23:59:59', $_now);
		}
		
		
		
		$_orders = \Mage::getModel('sales/order')->getCollection()
			->addAttributeToFilter('created_at', array('from'  => $_dateStart, 'to' => $_dateEnd))
			//->addAttributeToFilter('status', array('neq' => Mage_Sales_Model_Order::STATE_COMPLETE))
			->addAttributeToSelect('*') 
			->addAttributeToSort('created_at', 'DESC');
			;
							
		
		$this->set('collection',$_orders); 
	
	}
	
	 public function getOrdersByOrderID($_orderID,$_storeID=0)
	{
	
		$_orders = \Mage::getModel('sales/order')->getCollection()
			->addAttributeToFilter('increment_id', $_orderID)
			//->addAttributeToFilter('status', array('neq' => Mage_Sales_Model_Order::STATE_COMPLETE))
			->addAttributeToSelect('*')   
			->addAttributeToSort('created_at', 'DESC');
			;
							
		
		$this->set('collection',$_orders); 
	
	}
	
	public function getCustomerCountryByID($_id)
	{
		$_customer = Mage::getModel('customer/customer')->load($_id); 
		$_shippingaddress = Mage::getModel('customer/address')->load($_customer->default_shipping);
		$_addressdata = $_shippingaddress ->getData();
		
		if($_addressdata['country_id'])
		{
			$_country = Mage::getModel('directory/country')->loadByCode($_addressdata['country_id']);
			return ($_country->getName());
		}

		return ('NONE');
	}	
	
	public function getCustomerCountry($_id)
	{
	
		$_country = \Mage::getModel('directory/country')->loadByCode($_id);

		return ($_country->getName());
	}

	public function getCustomerRegion($_id)
	{
	
		$_region = \Mage::getModel('directory/region')->loadByCode($_id);

		return ($_region->getName());
	}		
}
?>