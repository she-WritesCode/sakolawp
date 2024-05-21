
(async function () {
	if (document.getElementById('peer_review_chart')) {
		const urlParams = new URLSearchParams(window.location.search);
		const homework_code = urlParams.get('homework_code');

		if (!homework_code) {
			console.error('No homework code provided in URL.');
			return
		}
		const requestUrl = `${skwp_ajax_object.ajaxurl}?action=sakolawp_peer_review_results&homework_code=${encodeURIComponent(homework_code)}`;
		fetch(requestUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
		})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					const datasets = data.data.flatMap((i) => {
						const item = Object.values(i);
						return item.map((j, index) => ({ label: Object.keys(i)[index], data: typeof j == 'string' ? (j ? 1 : 0) : j }))
					})
					console.log({
						labels: Object.keys(data.data[0]),
						datasets
					});
					// Handle the data here

					new Chart(
						document.getElementById('peer_review_chart'),
						{
							type: 'bar',
							data: {
								datasets: data.data.map(d => ({ data: d })),
							}
						}
					);
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