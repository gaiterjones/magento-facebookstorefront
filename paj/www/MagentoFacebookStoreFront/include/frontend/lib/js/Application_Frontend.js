// global vars for FRONTEND (not logged in)
//

var collectionPage=1,collectionCount=18,collectionTotalPages=1,collectionType='ALLPRODUCTS',loading=false;

jQuery(document).ready(function(){

	$(window).bind('scroll', function() {
		if($(window).scrollTop() >= $('.loader').offset().top + $('.loader').outerHeight() - window.innerHeight) {
			if (!loading)
			{
			   if (collectionPage < collectionTotalPages)
			   {
					collectionPage=collectionPage+1;
					console.log('loading page ' + collectionPage + '/' + collectionTotalPages);
					PAJ_Application_MagentoFacebookStoreFront_Magento_GetProducts(collectionPage,collectionCount,collectionType,true);

				} else {
					console.log('No more products to load');
				}

			}
		}
	});

	PAJ_Application_MagentoFacebookStoreFront_Magento_GetProducts(collectionPage,collectionCount,collectionType,false);

	PAJ_Application_MagentoFacebookStoreFront_Magento_GetCategories();

	// -- refresh / load PRODUCTS
	//
	$(".loader, .menu").on("click", ".refresh", function() {

		collectionTotalPages=1;
		collectionPage=1;

		PAJ_Application_MagentoFacebookStoreFront_Magento_GetProducts(collectionPage,collectionCount,collectionType,false);

		return false;
	});

	// -- category / load PRODUCTS
	//
	$(".menu").on("click", ".category", function() {

		collectionType='CATEGORY-' + $(this).data("id") + '-' + $(this).data("name");
		collectionName=$(this).data("name");
		$('#products .collection-name').html('<h2><i class="fa fa-asterisk"></i> ' + collectionName + '</h2>');
		collectionTotalPages=1;
		collectionPage=1;

		PAJ_Application_MagentoFacebookStoreFront_Magento_GetProducts(collectionPage,collectionCount,collectionType,false);

		return false;
	});

});
// -- get categories
//
function PAJ_Application_MagentoFacebookStoreFront_Magento_GetCategories()
{

		var ajaxVars =  {
							phpClassName: ['PAJ_Application_MagentoFacebookStoreFront_Magento_GetCategories'],
							phpClassVariableNames: ['languagecode'],
							phpClassVariableValues: [languagecode]
						};

		ajaxRequest(ajaxVars,'notifications',PAJ_Application_MagentoFacebookStoreFront_Magento_GetCategories_Callback,'index.php');


	return false;
}

// -- get products
//
function PAJ_Application_MagentoFacebookStoreFront_Magento_GetProducts(collectionPage,collectionCount,collectionType,collectionAppend)
{

		$('#products').toggleLoading({
			addText : 'Loading...'
		});

		$('.loader .refresh').addClass("fa-spin");

		loading=true;

		var ajaxVars =  {
							phpClassName: ['PAJ_Application_MagentoFacebookStoreFront_Magento_GetProducts'],
							phpClassVariableNames: ['collectionpage','collectioncount','collectiontype','collectionappend','languagecode'],
							phpClassVariableValues: [collectionPage,collectionCount,collectionType,collectionAppend,languagecode]
						};

		ajaxRequest(ajaxVars,'notifications',PAJ_Application_MagentoFacebookStoreFront_Magento_GetProducts_Callback,'index.php');


	return false;
}

// -- get producta callback
//
function PAJ_Application_MagentoFacebookStoreFront_Magento_GetProducts_Callback(successFlag,output,el) {

	$('#products').toggleLoading();
	$('.loader .info').empty();

	if ($('.loader .refresh').hasClass("fa-spin")) {
		setTimeout(function() {
			$('.loader .refresh').removeClass("fa-spin");

		}, 2000);
	}

	loading=false;

	if (successFlag) {

			collectionTotalPages=output.getMagentoProducts.collectionlastpagenumber;

			if (output.getMagentoProducts.html)
			{
					if ('false'===output.getMagentoProducts.collectionappend){	$('#products .product-data').empty();}

					$('#products .product-data').append(output.getMagentoProducts.html);

					var collectionNames=collectionType.split('-');

					var collectionName=collectionNames[0];

					if (collectionNames[2])
					{
						collectionName=collectionNames[2];
					}

					// auto loader
					//
					$('.loader .info').html('<span>showing ' + $('.product').length + ' of ' + output.getMagentoProducts.collectionsize + ' items ('  + collectionPage + '/' + collectionTotalPages + ')');

						$('.tooltip').tooltipster({
						   animation: 'grow',
						   iconTouch: true,
							 icon: 'More',
						   delay: 200,
						   theme: 'tooltipster-default',
						   touchDevices: true,
						   trigger: 'hover',
						   maxWidth: 300,
						   contentAsHTML: true,
						   offsetY: -50
						});

						try{
							FB.XFBML.parse();
						}catch(ex){}



			}


	} else { // error from ajax request

		error(output.output);
	}
}


// -- get categories callback
//
function PAJ_Application_MagentoFacebookStoreFront_Magento_GetCategories_Callback(successFlag,output,el) {


	if (successFlag) {

			if (output.getMagentoCategories.html)
			{

				$('#menu .categories').html(output.getMagentoCategories.html);

			}

	} else { // error from ajax request

		error(output.output);
	}
}



function error(message)
{
		$('#products .product-data').hide().html('<div id="error"><header><h1><i class="fa fa-exclamation-triangle"></i> ERROR</h1></header><h2>' + message + '</h2><p></p></div>').fadeIn("slow");
}