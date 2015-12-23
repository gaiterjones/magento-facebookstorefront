<?php
/**
 *  
 *  Copyright (C) 2014 paj@gaiterjones.com
 *
 * 	
 *
 */
 
namespace PAJ\Application\MagentoFacebookStoreFront\Page\Frontend\HTML\Template;

class Desktop {


function html()
{
$_HTML[] = array
	(
    'page' => array
    	(
	    	'*',
	    ),
    'html' => '
<!DOCTYPE HTML>
<html>
	<head>
		<title>BETA | Magento Facebook Storefront 2.0</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="description" content="'. $this->get('subpagemetadescription'). '" />
		<meta name="keywords" content="'. $this->get('subpagemetakeywords'). '" />
		<!--[if lte IE 8]><script src="include/frontend/template/Desktop/js/html5shiv.js"></script><![endif]-->
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script src="include/frontend/lib/js/Application_Main.js"></script>
		<script src="include/frontend/lib/js/Application_Frontend.js"></script>
		<script src="include/frontend/lib/plugins/tooltipster/js/jquery.tooltipster.min.js"></script>
		<script src="include/frontend/lib/plugins/toggleloading/toggleLoading.jquery.js"></script>
		<meta name="HandheldFriendly" content="True">
		<meta name="MobileOptimized" content="320">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="include/frontend/template/Desktop/css/html5reset.css" />
		<link rel="stylesheet" href="include/frontend/template/Desktop/css/style.css" />
		<link rel="stylesheet" href="include/frontend/lib/plugins/tooltipster/css/tooltipster.css" />
		<!--[if lte IE 8]><link rel="stylesheet" href="include/frontend/template/Desktop/css/ie8.css" /><![endif]-->
	</head>
'
);

// frontend html home page
$_HTML[] = array
	(
    'page' => array
    	(
	    	'*',
	    ),
    'html' => '
		<body class="frontend">
		<!-- Load Facebook SDK for JavaScript -->
		<div id="fb-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, \'script\', \'facebook-jssdk\'));
		var languagecode="'. $this->get('languagecode'). '";
		</script>
			
			<div id="wrapper">
			
				<div id="header">
					<h1>'. $this->get('applicationname'). '</h1>	
					<h2>'. $this->get('sitetitle'). '</h2>
				</div>
				
'
);


// frontend html home page
$_HTML[] = array
	(
    'page' => array
    	(
	    	'home',
	    ),
    'html' => '
			<div id="products">
			
				<div class="product-header section group">
					<div class="menu">
						<label for="show-menu" class="show-menu">Show Menu</label>
						<input type="checkbox" id="show-menu" role="button">
							<ul id="menu">
								<!--<li><a class="refresh" href="#"><i class="fa fa-refresh"></i></a></li>-->
								<li>
									<a href="#">'. $this->__t->__('Categories'). ' ￬</a>
									<ul class="categories hidden"></ul>
								</li>								
							</ul>
					</div>
				</div>
				
				<div class="clear"></div>
				<div class="collection-name"><h2><i class="fa fa-asterisk"></i> All products</h2></div>
				<div class="product-data section group"></div>
				
			</div>
			
			<div class="loader">
				<div class="section group">
					<p><span title="Reload" class="refresh"><i class="fa fa-refresh fa-4x"></i></span></p>
					<p class="info"></p>
				</div>
			</div>
'
);


// error meesage html
$_HTML[] = array
	(
    'page' => array
    	(
	    	'error',
	    ),
	'html' => '
		<div id="error">
		
				<header>
					<h1>Houston, we have a problem...</h1>
				</header>
				
				<h2>How very <strong>embarrassing</strong>, something has gone wrong:</h2>
				
				<p>'. $this->get('errorMessage'). '</p>
		</div>					
	'
	);	
	

// footer
$_HTML[] = array
	(
    'page' => array
    	(
	    	'*',
	    ),
	'html' => '
		<!-- Footer -->
			<div class="footer">
				<p>'. $this->get('version'). ' &copy; '. (new \DateTime())->format('Y'). ' ['. $this->get('languagecode'). ']</p>
			</div>

		</div><!-- Wrapper -->
	</body>
</html>
'
);





$this->set('html',$_HTML);
	
}


}
?>