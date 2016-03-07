<?php
/**
 *
 *  Copyright (C) 2016 paj@gaiterjones.com
 *
 *
 *
 */

namespace PAJ\Application\MagentoFacebookStoreFront\Magento;

/**
 * magento HTML class.
 *
 */
class HTML {


	public function Categories($_categories,$_categoriesProductCount)
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


	public function Products($_products,$_imageBaseURL)
	{
			$_columns=3;
			$_total=0;
			$_html='';
			$_productHtml='';
					
			$_taxRate=$this->getCurrentRate();
			
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
				$_name = self::clean_up($_product->getName());
				
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

		function getFirstSentence($content) {

	    $content = str_ireplace('<br />', ' - ', $content);
	    $content = html_entity_decode(strip_tags($content));
	    $pos = strpos($content, '.');

	    if($pos === false) {
	        return htmlentities($content);
	    }
	    else {
	        return htmlentities(substr($content, 0, $pos+1));
	    }

	}



/**
		 * clean_up function.
		 *
		 * @access private
		 * @param mixed $text
		 * @return void
		 */
		function clean_up ($text)
		{
			$cleanText=self::replaceHtmlBreaks($text," ");
			$cleanText=self::strip_html_tags($cleanText);
			$cleanText=preg_replace("/&#?[a-z0-9]+;/i"," ",$cleanText);
			$cleanText=htmlspecialchars($cleanText);

			return $cleanText;
		}

		/**
		 * strip_html_tags function.
		 *
		 * @access private
		 * @param mixed $text
		 * @return void
		 */
		function strip_html_tags( $text )
		{
		    $text = preg_replace(
		        array(
		          // Remove invisible content
		            '@<head[^>]*?>.*?</head>@siu',
		            '@<style[^>]*?>.*?</style>@siu',
		            '@<script[^>]*?.*?</script>@siu',
		            '@<object[^>]*?.*?</object>@siu',
		            '@<embed[^>]*?.*?</embed>@siu',
		            '@<applet[^>]*?.*?</applet>@siu',
		            '@<noframes[^>]*?.*?</noframes>@siu',
		            '@<noscript[^>]*?.*?</noscript>@siu',
		            '@<noembed[^>]*?.*?</noembed>@siu',
		          // Add line breaks before and after blocks
		            '@</?((address)|(blockquote)|(center)|(del))@iu',
		            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
		            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
		            '@</?((table)|(th)|(td)|(caption))@iu',
		            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
		            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
		            '@</?((frameset)|(frame)|(iframe))@iu',
		        ),
		        array(
		            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
		            "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
		            "\n\$0", "\n\$0",
		        ),
		        $text );
		    return strip_tags( $text );
		}

		/**
		 * replaceHtmlBreaks function.
		 *
		 * @access private
		 * @param mixed $str
		 * @param mixed $replace
		 * @param mixed $multiIstance (default: FALSE)
		 * @return void
		 */
		function replaceHtmlBreaks($str, $replace, $multiIstance = FALSE)
		{

		    $base = '<[bB][rR][\s]*[/]*[\s]*>';

		    $pattern = '|' . $base . '|';

		    if ($multiIstance === TRUE) {
		        //The pipe (|) delimiter can be changed, if necessary.

		        $pattern = '|([\s]*' . $base . '[\s]*)+|';
		    }

		    return preg_replace($pattern, $replace, $str);
		}

}
?>
