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
     * Get the API endpoint URL based on the environment setting
     *
     * @return string The API endpoint URL
     */
    public static function get_api_endpoint(): string {
        $environment = get_config('paygw_ezpay', 'environment');
        
        if ($environment === 'production') {
            return 'https://ezpay.iium.edu.my/payment/request';
        } else {
            // Default to staging
            return 'https://ezypay-stg.iium.edu.my/payment/request';
        }
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

        // Get the API endpoint based on environment
        $apiurl = self::get_api_endpoint();
        
        $mform->addElement('text', 'apiurl', get_string('apiurl', 'paygw_ezpay'));
        $mform->setType('apiurl', PARAM_URL);
        $mform->setDefault('apiurl', $apiurl);
        $mform->addHelpButton('apiurl', 'apiurl', 'paygw_ezpay');
        $mform->addRule('apiurl', get_string('required'), 'required', null, 'client');
        
        // Add service code field
        $mform->addElement('text', 'servicecode', get_string('servicecode', 'paygw_ezpay'));
        $mform->setType('servicecode', PARAM_TEXT);
        $mform->setDefault('servicecode', '001');
        $mform->addHelpButton('servicecode', 'servicecode', 'paygw_ezpay');
        
        // Add option to use redirect method instead of modal
        $mform->addElement('advcheckbox', 'useredirect', get_string('useredirect', 'paygw_ezpay'), 
            get_string('useredirect_desc', 'paygw_ezpay'));
        $mform->setType('useredirect', PARAM_BOOL);
        $mform->setDefault('useredirect', 1); // Set to true by default
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
        
        if (empty($data->servicecode)) {
            $errors['servicecode'] = get_string('required');
        }
    }
    
    /**
     * Get the list of actions that can be performed by this gateway.
     *
     * @return string[]
     */
    public function get_actions(): array {
        return [
            'ezpay/redirect',
        ];
    }
    
    /**
     * Determines if this gateway should use a redirect payment flow instead of a modal popup.
     *
     * @param int $accountid The account ID
     * @return bool
     */
    public static function uses_redirect(int $accountid): bool {
        $config = (object)self::get_gateway_configuration($accountid);
        return !empty($config->useredirect);
    }
}
