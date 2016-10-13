(function($) {
  'use strict';
  $(function() {
	$("#tabs").tabs({
	  activate: function (event, ui) {
		var scrollPos = $(window).scrollTop();
		window.location.hash = ui.newPanel.selector;
		$(window).scrollTop(scrollPos);
	  }
	});
  });
})(jQuery);
