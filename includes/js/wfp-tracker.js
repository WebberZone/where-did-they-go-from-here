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
		var data = new FormData();
		data.append('action', 'wherego_tracker');
		data.append('wfp_id', wfpTrackerArgs.wfp_id);
		data.append('wfp_sitevar', wfpTrackerArgs.wfp_sitevar);
		data.append('wfp_debug', wfpTrackerArgs.wfp_debug);
		data.append('wfp_rnd', wfpTrackerArgs.wfp_rnd);

		fetch(wfpTrackerArgs.ajax_url, {
			method: 'POST',
			body: data,
			credentials: 'same-origin',
		})
			.then(function (response) {
				if (wfpTrackerArgs.wfp_debug === 1) {
					return response.text();
				}
				return null;
			})
			.then(function (text) {
				if (wfpTrackerArgs.wfp_debug === 1 && text) {
					console.log('WFP Tracker:', text);
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
