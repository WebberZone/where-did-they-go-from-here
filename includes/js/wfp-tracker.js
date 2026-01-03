/**
 * WebberZone Followed Posts Tracker.
 *
 * Vanilla JavaScript tracker - no jQuery dependency.
 *
 * @package WebberZone\WFP
 * @since 3.2.0
 */

document.addEventListener('DOMContentLoaded', function () {
	'use strict';

	// Ensure wfpTrackerArgs is available.
	if (typeof wfpTrackerArgs === 'undefined') {
		return;
	}

	// Only track if we have a valid referrer from the same site.
	if (wfpTrackerArgs.wfp_sitevar && wfpTrackerArgs.wfp_id > 0) {
		var params = {
			action: 'wherego_tracker',
			wfp_id: wfpTrackerArgs.wfp_id,
			wfp_sitevar: wfpTrackerArgs.wfp_sitevar,
			wfp_debug: wfpTrackerArgs.wfp_debug,
			wfp_rnd: wfpTrackerArgs.wfp_rnd
		};

		fetch(wfpTrackerArgs.ajax_url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'Cache-Control': 'no-cache'
			},
			body: new URLSearchParams(params).toString()
		})
			.then(function (response) {
				if (!response.ok && 204 !== response.status) {
					throw new Error('Tracker request failed');
				}

				return response.text();
			})
			.then(function (data) {
				if (wfpTrackerArgs.wfp_debug === 1 && data) {
					console.log('WFP Tracker:', data);
				}
			})
			.catch(function (error) {
				if (wfpTrackerArgs.wfp_debug === 1) {
					console.error('WFP Tracker Error:', error);
				}
			});
	}
});
