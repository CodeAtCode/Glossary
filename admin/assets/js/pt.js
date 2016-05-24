(function ($) {
  'use strict';
  $(function () {
	function hide_text(val) {
	  if (val === 'external') {
		$('.cmb2-id-glossary-url').show();
		$('.cmb2-id-glossary-cpt').hide();
	  } else if (val === 'internal') {
		$('.cmb2-id-glossary-url').hide();
		$('.cmb2-id-glossary-cpt').show();
	  }
	}
	
	hide_text($('.cmb2-id-glossary-link-type input[type=radio]:checked').val());
	$('.cmb2-id-glossary-link-type input[type=radio]').change(function () {
	  hide_text(this.value);
	});
  });
})(jQuery);
