(function( $ ) {
	'use strict';

	if ($('#class_holder').length) {
		$('#class_holder').on( 'change', function () {
			var skwpClassVal = $('#class_holder').val();
			$.ajax({
				url: skwp_ajax_object.ajaxurl,
				type: 'POST',
				data: {
					action: 'sakolawp_select_section',
					class_id: skwpClassVal
				},
				success: function(response) {
					$('#section_holder').html(response);
				}
			});
		});
		$('#class_holder').on( 'change', function () {
			var skwpClassVal = $('#class_holder').val();
			$.ajax({
				url: skwp_ajax_object.ajaxurl,
				type: 'POST',
				data: {
					action: 'sakolawp_select_section2',
					class_id: skwpClassVal
				},
				success: function(response) {
					$('#section_holder2').html(response);
				}
			});
		});
	}

	if ($('#class_holder_spe').length) {
		$('#class_holder_spe').on( 'change', function () {
			var skwpClassVal = $('#class_holder_spe').val();
			$.ajax({
				url: skwp_ajax_object.ajaxurl,
				type: 'POST',
				data: {
					action: 'sakolawp_select_section_spe',
					class_id: skwpClassVal
				},
				success: function(response) {
					$('#section_holder_spe').html(response);
				}
			});
		});
	}

	if ($('#section_holder.teacher-section').length) {
		$('#section_holder.teacher-section').on( 'change', function () {
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
				success: function(response) {
					$('#subject_holder').html(response);
				}
			});
		});
	}

	if ($('#class_holder.select-subjects').length) {
		$('#class_holder.select-subjects').on( 'change', function () {
			var skwpClassVal = $('#class_holder').val();
			$.ajax({
				url: skwp_ajax_object.ajaxurl,
				type: 'POST',
				data: {
					action: 'sakolawp_select_all_subjects',
					class_id: skwpClassVal,
				},
				success: function(response) {
					$('#subject_holder').html(response);
				}
			});
		});
	}

	$('.skwp-menu-btn').click(function() {
		$(this).toggleClass("active");
		$('.sakolawp-navigation').toggleClass("open");
		$('.skwp-masking').toggleClass("open");
	});

	function updateCountdown() {
        $('.skwp-date').each(function() {
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
                    timeLeft = days + ' days ago';
                } else if (hours > 0) {
                    timeLeft = hours + ' hours ago';
                } else if (minutes > 0) {
                    timeLeft = minutes + ' minutes ago';
                } else {
                    timeLeft = seconds + ' seconds ago';
                }
            } else {
                // Future time
                if (days > 0) {
                    timeLeft = days + ' days left';
                } else if (hours > 0) {
                    timeLeft = hours + ' hours left';
                } else if (minutes > 0) {
                    timeLeft = minutes + ' minutes left';
                } else {
                    timeLeft = seconds + ' seconds left';
                }
            }

            $(this).text(timeLeft);
        });
    }

    // Update countdown every second
    setInterval(updateCountdown, 1000);

    // Initial update
    updateCountdown();

})( jQuery );