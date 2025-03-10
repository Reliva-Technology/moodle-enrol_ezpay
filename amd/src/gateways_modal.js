// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * IIUM EzPay payment gateway modal module.
 *
 * @module     paygw_ezpay/gateways_modal
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'jquery',
    'core/modal_factory',
    'core/modal_events',
    'core/str',
    'core/notification'
], function($, ModalFactory, ModalEvents, Str, Notification) {

    /**
     * Creates and shows a modal that contains the EzPay payment form.
     *
     * @param {String} url The URL for the payment form
     * @param {String} component The component name
     * @param {String} paymentArea The payment area
     * @param {Number} itemId The item ID
     * @param {String} description The payment description
     */
    var process = function(url, component, paymentArea, itemId, description) {
        // Get the required strings
        Str.get_string('pluginname', 'paygw_ezpay')
            .then(function(title) {
                return Str.get_string('redirectingtoezpay', 'paygw_ezpay')
                    .then(function(redirectText) {
                        // Create the modal
                        return ModalFactory.create({
                            type: ModalFactory.types.DEFAULT,
                            title: title,
                            body: '<div class="text-center"><p>' + redirectText + '</p>' +
                                  '<div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>',
                            large: true,
                        });
                    });
            })
            .then(function(modal) {
                // Handle hidden event
                modal.getRoot().on(ModalEvents.hidden, function() {
                    // Destroy when hidden
                    modal.destroy();
                });

                // Show the modal
                modal.show();

                // Redirect to payment page after showing the modal
                window.location.href = url + '?component=' + component + '&paymentarea=' + paymentArea + 
                                       '&itemid=' + itemId + '&description=' + encodeURIComponent(description);

                return modal;
            })
            .catch(Notification.exception);
    };

    /**
     * Initializes the payment process.
     *
     * @param {String} component The component name
     * @param {String} paymentArea The payment area
     * @param {Number} itemId The item ID
     * @param {String} description The payment description
     */
    var init = function(component, paymentArea, itemId, description) {
        // Get the redirect URL from the server
        var redirectUrl = M.cfg.wwwroot + '/payment/gateway/ezpay/redirect.php';
        process(redirectUrl, component, paymentArea, itemId, description);
    };

    return {
        init: init,
        process: process
    };
});
