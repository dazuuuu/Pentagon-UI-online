(function($) {
"use strict";

	jQuery(document).ready(function() {

		//START masonry
		jQuery(function ($) {

			var $nd_travel_masonry_content = $(".nd_travel_masonry_content").imagesLoaded( function() {
				$nd_travel_masonry_content.masonry({ itemSelector: ".nd_travel_masonry_item" });
			});

		});
		//END masonry
		
	});


})(jQuery);