(function ($) {
	'use strict';

	if ($('#class_holder').length) {
		$('#class_holder').on('change', function () {
			var skwpClassVal = $('#class_holder').val();
			$.ajax({
				url: skwp_ajax_object.ajaxurl,
				type: 'POST',
				data: {
					action: 'sakolawp_select_section',
					class_id: skwpClassVal
				},
				success: function (response) {
					$('#section_holder').html(response);
				}
			});
		});
		$('#class_holder').on('change', function () {
			var skwpClassVal = $('#class_holder').val();
			$.ajax({
				url: skwp_ajax_object.ajaxurl,
				type: 'POST',
				data: {
					action: 'sakolawp_select_section2',
					class_id: skwpClassVal
				},
				success: function (response) {
					$('#section_holder2').html(response);
				}
			});
		});
	}

	if ($('#class_holder_spe').length) {
		$('#class_holder_spe').on('change', function () {
			var skwpClassVal = $('#class_holder_spe').val();
			$.ajax({
				url: skwp_ajax_object.ajaxurl,
				type: 'POST',
				data: {
					action: 'sakolawp_select_section_spe',
					class_id: skwpClassVal
				},
				success: function (response) {
					$('#section_holder_spe').html(response);
				}
			});
		});
	}

	if ($('#section_holder.teacher-section').length) {
		$('#section_holder.teacher-section').on('change', function () {
			var skwpSectionVal = $('#section_holder').val(),
				skwpTeacherVal = $('#teacher_id_sel').val();
			$.ajax({
				url: skwp_ajax_object.ajaxurl,
				type: 'POST',
				data: {
					action: 'sakolawp_select_subject_teacher',
					section_id: skwpSectionVal,
					teacher_id: skwpTeacherVal
				},
				success: function (response) {
					$('#subject_holder').html(response);
				}
			});
		});
	}

	if ($('#class_holder.select-subjects').length) {
		$('#class_holder.select-subjects').on('change', function () {
			var skwpClassVal = $('#class_holder').val();
			$.ajax({
				url: skwp_ajax_object.ajaxurl,
				type: 'POST',
				data: {
					action: 'sakolawp_select_all_subjects',
					class_id: skwpClassVal,
				},
				success: function (response) {
					$('#subject_holder').html(response);
				}
			});
		});
	}

	$('.skwp-menu-btn').click(function () {
		$(this).toggleClass("active");
		$('.sakolawp-navigation').toggleClass("open");
		$('.skwp-masking').toggleClass("open");
	});

	if ($('.skwp-date').length) {
		// Update countdown every second
		setInterval(updateCountdown, 1000);

		// Initial update
		updateCountdown();
	}

	// When the page is ready, check if the checkbox is already checked
	if ($('#allow_peer_review').is(':checked')) {
		$('.peer-review-template-group').show();
		$('select[name="peer_review_template"]').prop('hidden', false);
		$('select[name="peer_review_template"]').prop('required', true);
		fetchPeerReviewTemplates()
	} else {
		$('.peer-review-template-group').hide();
		$('select[name="peer_review_template"]').prop('hidden', true);
		$('select[name="peer_review_template"]').prop('required', false);
	}

	// Add a change event listener to the checkbox
	$('#allow_peer_review').change(function () {
		if ($(this).is(':checked')) {
			$('.peer-review-template-group').show();
			$('select[name="peer_review_template"]').prop('hidden', false);
			$('select[name="peer_review_template"]').prop('required', true);
			fetchPeerReviewTemplates()
		} else {
			$('.peer-review-template-group').hide();
			$('select[name="peer_review_template"]').prop('required', true);
			$('select[name="peer_review_template"]').prop('hidden', false);
		}
	});

	// Function to fetch peer review templates options via AJAX
	function fetchPeerReviewTemplates() {
		var skwpTemplateVal = $('#peer_review_template').attr("data-value");
		$.ajax({
			url: skwp_ajax_object.ajaxurl,
			type: 'POST',
			data: {
				action: 'sakolawp_peer_review_templates_select_options',
				selected: skwpTemplateVal,
			},
			success: function (response) {
				$('#peer_review_template').html(response);
			},
			error: function (xhr, status, error) {
				console.error(error);
			}
		});
	}

	function updateCountdown() {
		$('.skwp-date').each(function () {
			var endDate = $(this).data('end-date');
			var endTime = $(this).data('end-time');
			var endDateTimeStr = endDate + ' ' + endTime;
			var endDateTime = new Date(endDateTimeStr);
			var currentDateTime = new Date();

			var timeDiff = endDateTime - currentDateTime;
			var seconds = Math.floor((timeDiff / 1000) % 60);
			var minutes = Math.floor((timeDiff / (1000 * 60)) % 60);
			var hours = Math.floor((timeDiff / (1000 * 60 * 60)) % 24);
			var days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));

			var timeLeft;
			if (timeDiff < 0) {
				// Past time
				timeDiff = -timeDiff;
				seconds = Math.floor((timeDiff / 1000) % 60);
				minutes = Math.floor((timeDiff / (1000 * 60)) % 60);
				hours = Math.floor((timeDiff / (1000 * 60 * 60)) % 24);
				days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));

				if (days > 0) {
					timeLeft = days + ' day(s) ago';
				} else if (hours > 0) {
					timeLeft = hours + ' hour(s) ago';
				} else if (minutes > 0) {
					timeLeft = minutes + ' minute(s) ago';
				} else {
					timeLeft = seconds + ' second(s) ago';
				}
			} else {
				// Future time
				if (days > 0) {
					timeLeft = days + ' day(s) left';
				} else if (hours > 0) {
					timeLeft = hours + ' hour(s) left';
				} else if (minutes > 0) {
					timeLeft = minutes + ' minute(s) left';
				} else {
					timeLeft = seconds + ' second(s) left';
				}
			}

			$(this).text(timeLeft);
		});
	}
})(jQuery);