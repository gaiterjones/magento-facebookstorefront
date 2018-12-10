<?php
/**
 *
 *  Copyright (C) 2016 paj@gaiterjones.com
 *
 * 	?ajax&class=PAJ_Application_MagentoFacebookStoreFront_Magento_GetCategories&variables=blah=blah
 *
 */

namespace PAJ\Application\MagentoFacebookStoreFront\Magento;

/**
 * magento GETPRODUCTS class.
 *
 * @extends Magento LOADER
 */
class GetCategories extends Loader {


	public function __construct($_variables) {

		$this->loadClassVariables($_variables);

		parent::__construct();

		$this->getMagentoCategegories();
	}

	private function getMagentoCategegories()
	{
		// init
		$this->set('success',false);
		$this->set('errormessage','No results found.');

		$_cached=false;
		$_output=false;

		$_languageCode=$this->get('languagecode');


		if ($this->__config->get('useMemcache'))
		{
			$_key=$this->__config->get('cacheKey'). '-CATEGORIES-'. $_languageCode;
			$_output=\PAJ\Library\Cache\Helper::getCachedString($_key,true);
		}

		if (!$_output)
		{
			$this->loadMagento($_languageCode);

			$_storeID=$this->get('storeid');

			$this->getCategoryCollection($_storeID,$this->__config->get('includeSubCategories'));

			$_categories=$this->get('categories');

			if ($_categories)
			{
				// render html
				$_html=$this->Categories($_categories,$this->get('categoriesproductcount'));

				$this->set('success',true);

				$_output=array(
					'getMagentoCategories' => array(
						'html' => $this->minify($_html,false,true)
					)
				);

				$this->set('output',$_output +  array('cached' => $_cached));

				if ($this->__config->get('useMemcache'))
				{
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


	protected function Categories($_categories,$_categoriesProductCount)
	{
		$_html='';

		foreach($_categories as $_category)
		{
			// check excluded categories
			//if (array_search($_category->getId(),$_excludedProductCategoriesArray)) { continue; }

			//
			// exclude default or root
			if(preg_match('/Root/', $_category->getName())) { continue; }
			if(preg_match('/Default/', $_category->getName())) { continue; }
			if(!$_category->getName()) { continue; }
			if ($_categoriesProductCount[$_category->getId()] > 0)
			{
				$_html=$_html.'
					<li>
						<a data-id="'. $_category->getId(). '" data-name="'. htmlspecialchars($_category->getName()). '" class="category" href="#">'. htmlspecialchars($this->truncate($_category->getName(),15,true)). ' ('. $_categoriesProductCount[$_category->getId()]. ')</a>
					</li>';
			}
		}

		return $_html;
	}

	private function getCategoryCollection($_storeID=0,$_includeChildren=true)
	{
		$_obj=new \PAJ\Library\Magento\Collection\Data();

		$_obj->getCategories($_storeID,$_includeChildren);

		$this->set('categories',$_obj->get('categories'));
		$this->set('categoriesproductcount',$_obj->get('categoriesproductcount'));

		unset ($_obj);

	}

}
?>
