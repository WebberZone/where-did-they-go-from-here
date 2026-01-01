/**
 * WebberZone Followed Posts Tracker.
 *
 * Vanilla JavaScript tracker - no jQuery dependency.
 *
 * @package WebberZone\WFP
 * @since 3.2.0
 */

(function () {
	'use strict';

	// Ensure wfpTrackerArgs is available.
	if (typeof wfpTrackerArgs === 'undefined') {
		return;
	}

	/**
	 * Send tracking data to the server.
	 */
	function sendTrackingData() {
		var isRestAPI = wfpTrackerArgs.ajax_url.indexOf('/wp-json/') !== -1;
		var fetchOptions = {
			method: 'POST',
			credentials: 'same-origin',
		};

		if (isRestAPI) {
			fetchOptions.headers = {
				'Content-Type': 'application/json',
			};
			fetchOptions.body = JSON.stringify({
				wfp_id: wfpTrackerArgs.wfp_id,
				wfp_sitevar: wfpTrackerArgs.wfp_sitevar,
			});
		} else {
			var data = new FormData();
			data.append('action', 'wherego_tracker');
			data.append('wfp_id', wfpTrackerArgs.wfp_id);
			data.append('wfp_sitevar', wfpTrackerArgs.wfp_sitevar);
			data.append('wfp_debug', wfpTrackerArgs.wfp_debug);
			data.append('wfp_rnd', wfpTrackerArgs.wfp_rnd);
			fetchOptions.body = data;
		}

		fetch(wfpTrackerArgs.ajax_url, fetchOptions)
			.then(function (response) {
				if (wfpTrackerArgs.wfp_debug === 1) {
					if (isRestAPI) {
						return response.json();
					}
					return response.text();
				}
				return null;
			})
			.then(function (result) {
				if (wfpTrackerArgs.wfp_debug === 1 && result) {
					console.log('WFP Tracker:', result);
				}
			})
			.catch(function (error) {
				if (wfpTrackerArgs.wfp_debug === 1) {
					console.error('WFP Tracker Error:', error);
				}
			});
	}

	// Only track if we have a valid referrer from the same site.
	if (wfpTrackerArgs.wfp_sitevar && wfpTrackerArgs.wfp_id > 0) {
		// Use requestIdleCallback if available, otherwise setTimeout.
		if ('requestIdleCallback' in window) {
			window.requestIdleCallback(sendTrackingData, { timeout: 2000 });
		} else {
			setTimeout(sendTrackingData, 100);
		}
	}
})();
