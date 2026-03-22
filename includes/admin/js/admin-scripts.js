/**
 * WebberZone Followed Posts Admin Scripts.
 *
 * @package WebberZone\WFP
 * @since 3.2.0
 */

(function ($) {
    'use strict';

    /**
     * Clear the cache.
     *
     * @since 3.2.0
     */
    window.clearCache = function () {
        // Get the button.
        const button = document.getElementById('cache_clear');

        // Disable button and change text.
        if (button) {
            button.disabled = true;
            button.value = wherego_admin_data.strings.clearing_cache;

            // Add spinner.
            const spinner = document.createElement('span');
            spinner.className = 'spinner is-active';
            spinner.style.float = 'none';
            spinner.style.marginTop = '0';
            button.parentNode.insertBefore(spinner, button.nextSibling);
        }

        // Send AJAX request.
        $.post(
            ajaxurl,
            {
                action: 'wherego_clear_cache',
                security: wherego_admin_data.security,
            },
            function (response) {
                // Remove spinner.
                if (button) {
                    const spinner = button.nextSibling;
                    if (spinner && spinner.classList.contains('spinner')) {
                        spinner.remove();
                    }

                    // Re-enable button and restore text.
                    button.disabled = false;
                    button.value = wherego_admin_data.strings.clear_cache;
                }

                // Show response message.
                if (response.success) {
                    alert(response.data.message);
                } else {
                    alert(response.data.message || 'Failed to clear cache.');
                }
            },
            'json'
        ).fail(function () {
            // Remove spinner.
            if (button) {
                const spinner = button.nextSibling;
                if (spinner && spinner.classList.contains('spinner')) {
                    spinner.remove();
                }

                // Re-enable button and restore text on error.
                button.disabled = false;
                button.value = wherego_admin_data.strings.clear_cache;
            }
            alert('Request failed. Please try again.');
        });

        return false;
    };
})(jQuery);
