/**
 * BannerMonster - Admin JS
 */
(function ($) {
	'use strict';

	$(document).ready(function () {
		// Display rules toggle
		var $display = $('#bannermonster_display_where');
		function toggleDisplay() {
			var v = $display.val();
			$('.bannermonster-display-section').hide().filter('[data-show="' + v + '"]').show();
		}
		$display.on('change', toggleDisplay);
		toggleDisplay();

		// Trigger toggle
		var $trigger = $('#bannermonster_trigger');
		function toggleTrigger() {
			var v = $trigger.val();
			$('.bannermonster-trigger-opt').hide().filter('[data-trigger="' + v + '"]').show();
		}
		$trigger.on('change', toggleTrigger);
		toggleTrigger();

		// Color picker
		if ($.fn.wpColorPicker) {
			$('.bannermonster-color').wpColorPicker();
		}
	});
})(jQuery);
