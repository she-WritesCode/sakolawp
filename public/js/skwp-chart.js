jQuery(document).ready(function ($) {
	(async function () {
		$('.toggle-review').each(function () {
			const toggleReview = $(this);
			const toggleReviewHandle = toggleReview.next('.toggle-review-handle');
			if (!toggleReviewHandle.is(':hidden')) {
				toggleReviewHandle.hide();
			}
			toggleReview.click(function () {
				if (toggleReviewHandle.is(':hidden')) {
					toggleReviewHandle.show();
				} else {
					toggleReviewHandle.hide();
				}
			});
		});
		if (document.getElementById('peer_review_chart') || document.getElementById('mean_review_summary') || document.getElementById('peer_review_chart2')) {
			const urlParams = new URLSearchParams(window.location.search);
			const homework_code = urlParams.get('homework_code') || $('#mean_review_summary').data('student_id');
			const student_id = $('#mean_review_summary').data('student_id');

			if (!homework_code) {
				console.error('No homework code provided in URL.');
				return
			}
			let requestUrl = `${skwp_ajax_object.ajaxurl}?action=sakolawp_peer_review_results`;
			requestUrl += `&homework_code=${encodeURIComponent(homework_code)}`;
			if (student_id) {
				requestUrl += `&student_id=${encodeURIComponent(student_id)}`;
			}
			fetch(requestUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
			})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						const ctx = document.getElementById('peer_review_chart')?.getContext('2d');
						const ctx2 = document.getElementById('peer_review_chart2')?.getContext('2d');

						const datasets = data.data.dataSets.map((dataPoints, index) => ({
							label: `Review ${index + 1}`,
							data: dataPoints,
							// backgroundColor: `rgba(54, 162, 235, 0.${index + 2})`,
							// borderColor: `rgba(54, 162, 235, 1)`,
							borderWidth: 1
						}));
						if (ctx) {
							new Chart(ctx, {
								type: 'radar',
								data: {
									labels: data.data.labels,
									datasets: datasets
								},
								options: {
									scale: {
										ticks: {
											beginAtZero: true,
											max: 100
										}
									}
								}
							});
						}
						if (ctx2) {
							new Chart(ctx2, {
								type: 'bar',
								data: {
									labels: data.data.labels,
									datasets: datasets
								},
								options: {
									indexAxis: 'y',
									responsive: true,
									scales: {
										y: {
											beginAtZero: true,
											max: 100
										}
									}
								}
							});
						}
						// Display textual summary
						// const summaryContainer = $('#peer_review_summary');
						// data.data.summary.forEach((summaryItem, index) => {
						// 	const summaryDiv = $('<div>').addClass('summary-item');
						// 	summaryDiv.append(`<h5>Review ${index + 1}</h5>`);
						// 	summaryItem.forEach(item => {
						// 		summaryDiv.append(`<p>${item}</p>`);
						// 	});
						// 	summaryContainer.append(summaryDiv);
						// });

						// Display mean scores
						const meanSummaryContainer = $('#mean_review_summary');
						meanSummaryContainer.append('<h5>Average Scores per Question</h5>');
						data.data.labels.forEach((label, index) => {
							const questionId = `q${index + 1}`;
							const meanScore = data.data.meanScores[questionId];
							meanSummaryContainer.append(`<div>${label}: ${(meanScore * 10).toFixed(2)}%</div>`);
						});
					} else {
						console.error(data.data);
						// Handle the error here
					}
				})
				.catch(error => {
					console.error('Error:', error);
				});
		}
	})();
})