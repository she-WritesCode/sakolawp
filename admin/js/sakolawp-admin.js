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

		if ($('#generate_qr_code').length) {
			$('#generate_qr_code').on('click', function () {
				const skwpEventId = $('#generate_qr_code').attr("data-event_id");
				$.ajax({
					url: skwp_ajax_object.ajaxurl,
					type: 'POST',
					data: {
						action: 'sakolawp_generate_qr_code',
						event_id: skwpEventId,
					},
					success: function (response) {
						$('#qr_code_holder').html(`<img src="${response.data.image}" />`);
						downloadImage(response.data.image, `event-${skwpEventId}-qrcode`);
					}
				});
			})
		}

		// Function to download the image
		function downloadImage(imageUrl, fileName) {
			// Create a temporary link element
			const link = document.createElement('a');
			link.href = imageUrl;
			link.download = fileName;

			// Append the link to the document body
			document.body.appendChild(link);

			// Programmatically click the link to trigger the download
			link.click();

			// Remove the link from the document
			document.body.removeChild(link);
		}
	});

})(jQuery);
