(function ($) {
	'use strict';

	if ($('#dataTable1').length) {
		$('#dataTable1').DataTable({
			order: [[0, 'asc']],
			pageLength: 25,
		});
	}
	if ($('table[id^="dataTable-"]').length) {
		$('table[id^="dataTable-"]').each(function () {
			$(this).DataTable({
				order: [[0, 'asc']],
				pageLength: 25,
				responsive: true,
			});

		})
	}

	$('#from_date').prop('max', function () {
		return new Date().toJSON().split('T')[0];
	});
	$('#to_date').prop('max', function () {
		return new Date().toJSON().split('T')[0];
	});


	if ($('#class_holder').length) {
		$('#class_holder').on('change', function () {
			var skwpClassVal = $('#class_holder').val();
			var skwpSectionVal = $('#section_holder').attr("data-value");
			$.ajax({
				url: skwp_ajax_object.ajaxurl,
				type: 'POST',
				data: {
					action: 'sakolawp_select_section',
					class_id: skwpClassVal,
					selected: skwpSectionVal
				},
				success: function (response) {
					$('#section_holder').html(response);
				}
			});
		});
	}

	if ($('#section_holder').length) {
		$('#section_holder').on('change', function () {
			var skwpSectionVal = $('#section_holder').val();
			var skwpClassVal = $('#class_holder').val();
			$.ajax({
				url: skwp_ajax_object.ajaxurl,
				type: 'POST',
				data: {
					action: 'sakolawp_select_subject',
					section_id: skwpSectionVal,
					class_id: skwpClassVal
				},
				success: function (response) {
					$('#subject_holder').html(response);
				}
			});
		});
	}

	if ($('#section_holder').length && $('#accountability_holder').length) {
		$('#section_holder').on('change', function () {
			var skwpSectionVal = $('#section_holder').val();
			$.ajax({
				url: skwp_ajax_object.ajaxurl,
				type: 'POST',
				data: {
					action: 'sakolawp_select_accountability',
					section_id: skwpSectionVal,
				},
				success: function (response) {
					$('#accountability_holder').html(response);
				}
			});
		});
	}

	if ($('#section_holder.first').length) {
		var skwpClass = $("#class_id").val();
		var skwpSubject = $("#subject_id").val();
		$.ajax({
			url: skwp_ajax_object.ajaxurl,
			type: 'POST',
			data: {
				action: 'sakolawp_select_section_first',
				class_id: skwpClass,
				subject_id: skwpSubject,
			},
			success: function (response) {
				$('#section_holder').html(response);
			}
		});
	}

})(jQuery);