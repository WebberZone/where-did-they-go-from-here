jQuery(document).ready(function($) {
	var data = {
		'action': 'wherego_tracker',
		'wherego_nonce': ajax_wherego_tracker.wherego_nonce,
		'wherego_id': ajax_wherego_tracker.wherego_id,
		'wherego_sitevar': ajax_wherego_tracker.wherego_sitevar
	};
	jQuery.post(ajax_wherego_tracker.ajax_url, data );
});