(function ($) {
	'use strict';

	var windowHeight = $(window).width();

	$(document).ready(function () {
		if ($('.tab-pane').length) {
			$(".nav-item:first-child .nav-link").addClass("active");
			$(".tab-pane:first-child").addClass("active");
		}

		if ($('#mySelect').length) {
			$('#mySelect').on('change', function (e) {
				var $optionSelected = $("option:selected", this);
				$optionSelected.tab('show')
			});
		}

		if ($('input.single-daterange').length) {
			$('input.single-daterange').daterangepicker({
				"singleDatePicker": true
			});
		}

		if ($('input.single-daterange2').length) {
			$('input.single-daterange2').daterangepicker({
				"singleDatePicker": true
			});
		}

		if ($('input.multi-daterange').length) {
			$('input.multi-daterange').daterangepicker({
				"startDate": "03/28/2024",
				"endDate": "04/06/2024"
			});
		}
	});

})(jQuery);
