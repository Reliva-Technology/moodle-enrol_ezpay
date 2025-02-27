<?php
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
 * Contains the gateway class for IIUM EzPay payment gateway.
 *
 * @package    paygw_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_ezpay;

use core_payment\form\account_gateway;

/**
 * The gateway class for IIUM EzPay payment gateway.
 *
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gateway extends \core_payment\gateway {
    /**
     * The full list of currencies supported by IIUM EzPay.
     *
     * @return string[]
     */
    public static function get_supported_currencies(): array {
        return ['MYR'];
    }

    /**
     * Configuration form for the gateway instance
     *
     * @param account_gateway $form The form instance
     */
    public static function add_configuration_to_gateway_form(account_gateway $form): void {
        $mform = $form->get_mform();

        $mform->addElement('text', 'merchantcode', get_string('merchantcode', 'paygw_ezpay'));
        $mform->setType('merchantcode', PARAM_TEXT);
        $mform->addHelpButton('merchantcode', 'merchantcode', 'paygw_ezpay');
        $mform->addRule('merchantcode', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'apiurl', get_string('apiurl', 'paygw_ezpay'));
        $mform->setType('apiurl', PARAM_URL);
        $mform->setDefault('apiurl', 'https://ezpay.iium.edu.my/payment/request');
        
        // Add help text directly as a static element
        $helptext = 'The API URL is the endpoint where payment requests will be sent. The default URL is the production endpoint. Change this only if you need to use a different endpoint for testing or if instructed by IIUM EzPay support.';
        $mform->addElement('static', 'apiurl_help_text', '', $helptext);
        
        $mform->addRule('apiurl', get_string('required'), 'required', null, 'client');
    }

    /**
     * Validates the gateway configuration form.
     *
     * @param account_gateway $form The submitted form
     * @param \stdClass $data The submitted data
     * @param array $files The submitted files
     * @param array $errors The list of errors
     */
    public static function validate_gateway_form(account_gateway $form, \stdClass $data, array $files, array &$errors): void {
        if (empty($data->merchantcode)) {
            $errors['merchantcode'] = get_string('required');
        }

        if (empty($data->apiurl)) {
            $errors['apiurl'] = get_string('required');
        }
    }
}
