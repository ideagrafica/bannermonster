/**
 * BannerMonster - Admin JS
 */
(function ($) {
	'use strict';

	$(document).ready(function () {
		// Display rules toggle
		var $display = $('#bm_display_where');
		function toggleDisplay() {
			var v = $display.val();
			$('.bm-display-section').hide().filter('[data-show="' + v + '"]').show();
		}
		$display.on('change', toggleDisplay);
		toggleDisplay();

		// Trigger toggle
		var $trigger = $('#bm_trigger');
		function toggleTrigger() {
			var v = $trigger.val();
			$('.bm-trigger-opt').hide().filter('[data-trigger="' + v + '"]').show();
		}
		$trigger.on('change', toggleTrigger);
		toggleTrigger();

		// Color picker
		if ($.fn.wpColorPicker) {
			$('.bm-color').wpColorPicker();
		}
	});
})(jQuery);
