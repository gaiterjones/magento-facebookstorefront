(function($){
	
	var speed = 200,
	ease = 'linear';
 
    $.fn.extend({ 


		// Take a wrapper and show thats its laoding something
		// this places a div over the top of the content to prevent interaction.
		toggleLoading : function(config) {
			
			// Were additional class & text provided?
			if (config === undefined) {
				var addClass = false,
				addText = false;
				
			} else {		
				var addClass = config.addClass,
				addText = config.addText;
				
			}		
			
			

			return this.each( function() {
				
				// The element that will be loading
				var $wrapper = $(this),
				
				// The div that make the item above appear to be loading
				$loader = $('#loading_' + $wrapper.data('loading-id') );
				
				
				// The loading div exists & is visible
				// Hide it
				if ( $loader.length && $loader.is(':visible') ) {

					// Fade the wrapper back in
					$wrapper.stop(true, true).animate({"opacity" : 1}, speed * 0.5, ease)
					
					// Hide the loader
					$loader.hide();
						
				
				// The loading div's not been created yet	
				// Create the markup, insert into dom, position, then show	
				} else if ( !( $loader.length ) ) {
					
					// A random ID
					// We use this to asscocoiate the wrapper with its loading div
					var id =  Math.floor( Math.random() * 10000 ),
					
					// The HTML to insert
					html_string = '<div class="toggle-loading' + ( ( addClass ) ? ' ' + addClass : '') + '" id="loading_' + id + '"><span class="icon"></span>' + ( ( addText ) ? '<span class="copy">' + addText + '</span>' : '') + '</div>',
					// The position of the wrapper on the page
					position = $wrapper.offset();
					
					// Add a data attribute to the wrapper & fade the content that is loading out
					// this matches this wrapper to the id of the loading div
					$wrapper.attr('data-loading-id', id ).stop(true, true).animate({'opacity' : 0.05}, speed * 0.5, ease);
					
					// Add the loading div, give it an id & position it over the wrapper.
					$('body').append(html_string);
					$('#loading_' + id).css({ "width" : $wrapper.width(), "height" : $wrapper.height(), "left" : position.left, "top" : position.top }).stop(true, true).show();
					
					
				
				// The loading div exists but is hidden.
				// Position it then show	
				} else {
					

					// The position of the wrapper on the page
					var position = $wrapper.offset();

					// Fade the content that is loading out
					$wrapper.stop(true, true).animate({'opacity' : 0.05}, speed * 0.5, ease);

					// Add the loading div, give it an id & position it over the wrapper.
					$loader.css({ "width" : $wrapper.width(), "height" : $wrapper.height(), "left" : position.left, "top" : position.top }).show();
					
				}
				
				

				
			})
			
		}
        


    }); 
})(jQuery);





