<?php
/**
 *
 *  Copyright (C) 2016 paj@gaiterjones.com
 *
 * 	?ajax&class=PAJ_Application_MagentoFacebookStoreFront_Magento_GetProducts&variables=blah=blah
 *
 */

namespace PAJ\Application\MagentoFacebookStoreFront\Magento;

/**
 * magento GETPRODUCTS class.
 *
 * @extends Magento LOADER
 */
class GetProducts extends Loader {


	public function __construct($_variables) {

		$this->loadClassVariables($_variables);

		parent::__construct();

		$this->getMagentoProducts();
	}

	private function getMagentoProducts()
	{
		// init
		$this->set('success',false);
		$this->set('errormessage','No results found.');

		$_cached=false;
		$_output=false;

		$_languageCode=$this->get('languagecode');

		$_page=$this->get('collectionpage');
		$_count=$this->get('collectioncount');
		$_type=$this->get('collectiontype');

		if ($this->__config->get('useMemcache'))
		{
			$_key=$this->__config->get('cacheKey'). '-PRODUCTS-'. md5($_languageCode.$_page.$_count.$_type);
			$_output=\PAJ\Library\Cache\Helper::getCachedString($_key,true);
		}

		if (!$_output)
		{
			$this->loadMagento($_languageCode);

			$_storeID=$this->get('storeid');

			$this->getProductCollection($_storeID,$_page,$_count,$_type);

			$_products=$this->get('collection');

			if ($_products)
			{


				// render html
				$_html=$this->Products($_products,$this->get('baseurlmedia'),$this->getCurrentRate());

				$this->set('success',true);

				$_output=array(
					'getMagentoProducts' => array(
						'html' => $this->minify($_html,false,true),
						'collectionappend' => $this->get('collectionappend'),
						'collectionpage' => $_page,
						'productcount' => count($_products),
						'collectionlastitemid' => $this->get('collectionlastitemid'),
						'collectionlastpagenumber' => $this->get('collectionlastpagenumber'),
						'collectionsize' => $this->get('collectionsize')
					)
				);

				$this->set('output',$_output +  array('cached' => $_cached));

				if ($this->__config->get('useMemcache')) {
					\PAJ\Library\Cache\Helper::setCachedString($_output,$_key,3600);
				}

			} else {

				$this->set('errormessage','No results found.');

			}

		} else {

			$_cached=true;
			$this->set('success',true);
			$this->set('output',$_output +  array('cached' => $_cached));
		}

	}

	private function getProductCollection($_storeID=0,$_page=1,$_count=18,$_type='ALLPRODUCTS')
	{
		$_obj=new \PAJ\Library\Magento\Collection\Data();

		$_type=explode('-',$_type);


		if ('CATEGORY'===$_type[0]){

			$_obj->getCategoryProducts($_storeID,$_type[1],$_page,$_count);

		} else {
			$_obj->getAllProducts($_storeID,$_page,$_count);
		}


		$this->set('collection',$_obj->get('collection'));
		$this->set('collectionsize',$_obj->get('collectionsize'));
		$this->set('collectionlastitemid',$_obj->get('collectionlastitemid'));
		$this->set('collectionlastpagenumber',$_obj->get('collectionlastpagenumber'));

		$this->set('baseurlmedia',$_obj->get('baseurlmedia'));

		unset ($_obj);

	}

	protected function Products($_products,$_imageBaseURL,$_taxRate)
	{
			$_columns=3;
			$_total=0;
			$_html='';
			$_productHtml='';

			foreach($_products as $_id=>$_product)
			{

				// -- product variables

				// -- sku
				$_sku = $_product->getSku();

				// -- formatted price
				$_priceHtml='<p></p>';

				if ($this->__config->get('showProductPrice'))
				{

					// grouped and bundled price
					//
					if( $_product->getTypeId() == 'grouped' || $_product->getTypeId() == 'bundle')
					{
						$_formattedPrice=GetProductData::formattedGroupedPrice($_product,$_taxRate);

						if ($_formattedPrice)
						{
							list($_intPrice, $_decPrice) = explode('.', $_formattedPrice);
							$_priceHtml='
								<p class="priceinfo" title="'. $this->__t->__('price'). ($_taxRate ? ' incl. tax':''). '">
									<span class="grouped">'. $this->__t->__('from'). ' </span>
									<strong class="price">
										<span class="currency"> </span>'. $_intPrice.'<span class="decimals">.'. $_decPrice.'</span>
									</strong>
								</p>';
						}

					} else {

					// simple / configurable price
					//
						$_formattedPrice=GetProductData::formattedPrice($_product,$_taxRate);

						if ($_formattedPrice)
						{
							list($_intPrice, $_decPrice) = explode('.', $_formattedPrice);
							$_priceHtml='
								<p class="priceinfo" title="'. $this->__t->__('price'). ($_taxRate ? ' incl. tax':''). '">
									<strong class="price">
										<span class="currency"> </span>'. $_intPrice.'<span class="decimals">.'. $_decPrice.'</span>
									</strong>
								</p>';
						}
					}


				}

				// -- name
				$_name = HTML::clean_up($_product->getName());

				// -- description text
				if ($this->__config->get('useLongDescriptions')) {
					$_descriptionText=$_product->getDescription();
				} else {
					$_descriptionText=$_product->getShortDescription();
				}

				// -- urls
				$_url=explode('?',$_product->getProductUrl());
				$_url=$_url[0];
				$_imageURL=$_imageBaseURL. 'catalog/product/'. $_product->getSmall_image();

				// force https
				//
				//$_url = preg_replace("/^http:/i", "https:", $_url);
				//$_imageURL = preg_replace("/^http:/i", "https:", $_imageURL);

				$_id=$_product->getId();

				$_productIsSaleable= $_product->isSaleable();

				$_productHtml=$_productHtml.'
					<div class="col span_1_of_'. $_columns. '">
						<div class="section group product id-'. $_id. '">

							<div class="container">

								<div class="info">

									'. GetProductData::getProductLabel($_product). '

									<div class="name">
										<p>'.$this->truncate($_name,35,true). '</p>
										<ul class="links">
											<li>
												'. $_priceHtml. '
											</li>
											<li>
												<p>
													<a target="parent" title="'. $this->__t->__('Buy Now'). '" href="'. $_url. '">
														<div class="basket">
															<span class="fa-stack fa-1x">
															  <i class="fa fa-circle fa-stack-2x"></i>
															  <i class="fa fa-shopping-cart fa-stack-1x fa-inverse"></i>
															</span>
														</div>
													</a>
												</p>
											</li>
											<li>
											<div class="fb-like-wrapper">
												<div class="fb-like" data-href="'. $_url. '" data-send="false" data-action="like" data-layout="button_count"  data-show-faces="false"></div>
											</div>
											</li>
										</ul>
									</div>

									<div>
										<p class="image center clear">
											<a target="parent" href="'. $_url. '">
												<img class="tooltip" title="'.$this->truncate($_descriptionText,250).'" alt="'.$_name.'" src="'. $_imageURL. '">
											</a>
										</p>
									</div>


									<div class="bottom">

									</div>
								</div>

							</div>

						</div>
					</div>
				';

				$_total ++;

				if ($_total % 3 == 0 || $_total == count($_products)) {
					$_html=$_html.'<div class="section group">'. $_productHtml. '</div>';
					$_productHtml='';
				}


			}


			return $_html;

	}

	/**
	*  Get current tax rate
	*
	* @return  float   $rate
	*/
	public function getCurrentRate(){

		$_calc = \Mage::getSingleton('tax/calculation');
		$_rates = $_calc->getRatesForAllProductTaxClasses($_calc->getRateRequest());

		foreach ($_rates as $_class=>$_rate) {
		   $_result = $_rate;
		}

		return floatval($_result);
	}

}
?>
