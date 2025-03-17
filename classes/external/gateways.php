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
 * External functions for payment gateways
 *
 * @package    paygw_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_ezpay\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use moodle_exception;
use moodle_url;

/**
 * External function for payment gateways
 *
 * @package    paygw_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gateways extends external_api {

    /**
     * Returns description of get_available_gateways() parameters.
     *
     * @return external_function_parameters
     */
    public static function get_available_gateways_parameters() {
        return new external_function_parameters(
            [
                'component' => new external_value(PARAM_COMPONENT, 'Component'),
                'paymentarea' => new external_value(PARAM_AREA, 'Payment area in the component'),
                'itemid' => new external_value(PARAM_INT, 'An identifier for payment area in the component'),
            ]
        );
    }

    /**
     * Get available gateways.
     *
     * @param string $component
     * @param string $paymentarea
     * @param int $itemid
     * @return array
     */
    public static function get_available_gateways($component, $paymentarea, $itemid) {
        global $USER;

        self::validate_parameters(self::get_available_gateways_parameters(),
            ['component' => $component, 'paymentarea' => $paymentarea, 'itemid' => $itemid]);

        $payable = new \core_payment\payable($component, $paymentarea, $itemid);
        $accounts = \core_payment\account::find_all_for_payable($payable);
        $gateways = [];

        foreach ($accounts as $account) {
            $gateway = \core_payment\helper::get_gateway_info($account->get('gateway'));
            if ($gateway && $gateway->gateway === 'ezpay') {
                $config = (object) \paygw_ezpay\gateway::get_gateway_configuration($account->get('id'));
                $useredirect = !empty($config->useredirect);

                $gateways[] = [
                    'shortname' => $gateway->gateway,
                    'name' => get_string('gatewayname', 'paygw_ezpay'),
                    'description' => get_string('gatewaydescription', 'paygw_ezpay'),
                    'useredirect' => $useredirect,
                    'accountid' => $account->get('id'),
                ];
            }
        }

        return $gateways;
    }

    /**
     * Returns description of get_available_gateways() result value.
     *
     * @return external_multiple_structure
     */
    public static function get_available_gateways_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'shortname' => new external_value(PARAM_PLUGIN, 'Gateway shortname'),
                    'name' => new external_value(PARAM_TEXT, 'Gateway name'),
                    'description' => new external_value(PARAM_TEXT, 'Gateway description'),
                    'useredirect' => new external_value(PARAM_BOOL, 'Whether to use redirect method'),
                    'accountid' => new external_value(PARAM_INT, 'Account ID'),
                ]
            )
        );
    }

    /**
     * Returns description of get_configuration_form() parameters.
     *
     * @return external_function_parameters
     */
    public static function get_configuration_form_parameters() {
        return new external_function_parameters(
            [
                'component' => new external_value(PARAM_COMPONENT, 'Component'),
                'paymentarea' => new external_value(PARAM_AREA, 'Payment area in the component'),
                'itemid' => new external_value(PARAM_INT, 'An identifier for payment area in the component'),
                'accountid' => new external_value(PARAM_INT, 'The payment account ID'),
            ]
        );
    }

    /**
     * Get configuration form.
     *
     * @param string $component
     * @param string $paymentarea
     * @param int $itemid
     * @param int $accountid
     * @return string
     */
    public static function get_configuration_form($component, $paymentarea, $itemid, $accountid) {
        global $OUTPUT;

        self::validate_parameters(self::get_configuration_form_parameters(),
            ['component' => $component, 'paymentarea' => $paymentarea, 'itemid' => $itemid, 'accountid' => $accountid]);

        $config = (object) \paygw_ezpay\gateway::get_gateway_configuration($accountid);
        $useredirect = !empty($config->useredirect);

        if ($useredirect) {
            // For redirect method, we just need to redirect to the payment page
            $redirecturl = new moodle_url('/payment/gateway/ezpay/redirect.php', [
                'component' => $component,
                'paymentarea' => $paymentarea,
                'itemid' => $itemid,
            ]);

            return json_encode([
                'form' => $OUTPUT->render_from_template('paygw_ezpay/redirect_form', [
                    'redirecturl' => $redirecturl->out(false),
                ])
            ]);
        } else {
            // For modal method, we need to show a form with a button that will trigger the modal
            return json_encode([
                'form' => $OUTPUT->render_from_template('paygw_ezpay/modal_form', [
                    'component' => $component,
                    'paymentarea' => $paymentarea,
                    'itemid' => $itemid,
                ])
            ]);
        }
    }

    /**
     * Returns description of get_configuration_form() result value.
     *
     * @return external_value
     */
    public static function get_configuration_form_returns() {
        return new external_value(PARAM_RAW, 'Form HTML');
    }
}
