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
 * Contains the transaction class for IIUM EzPay payment gateway.
 *
 * @package    paygw_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_ezpay\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;

/**
 * The transaction class for IIUM EzPay payment gateway.
 *
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class transaction extends external_api {

    /**
     * Returns description of method parameters for initiating payment transaction.
     *
     * @return external_function_parameters
     */
    public static function initiate_parameters(): external_function_parameters {
        return new external_function_parameters([
            'component' => new external_value(PARAM_COMPONENT, 'Component'),
            'paymentarea' => new external_value(PARAM_AREA, 'Payment area in the component'),
            'itemid' => new external_value(PARAM_INT, 'An identifier for payment area in the component'),
            'description' => new external_value(PARAM_TEXT, 'Payment description')
        ]);
    }

    /**
     * Initiate payment transaction and return response from IIUM EzPay.
     *
     * @param string $component
     * @param string $paymentarea
     * @param int $itemid
     * @param string $description
     * @return array
     */
    public static function initiate(string $component, string $paymentarea, int $itemid, string $description): array {
        global $USER, $DB;

        $params = self::validate_parameters(self::initiate_parameters(), [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
            'description' => $description
        ]);

        $config = (object) get_config('paygw_ezpay');
        $payable = \core_payment\helper::get_payable($params['component'], $params['paymentarea'], $params['itemid']);
        $surcharge = \core_payment\helper::get_gateway_surcharge('ezpay');
        $amount = \core_payment\helper::get_rounded_cost($payable->get_amount(), $payable->get_currency(), $surcharge);

        $data = [
            'TRANS_ID' => $itemid,
            'MERCHANT_CODE' => $config->merchantcode,
            'RETURN_URL' => (new \moodle_url('/payment/gateway/ezpay/process.php'))->out(false),
            'AMOUNT' => $amount,
            'EMAIL' => $USER->email,
            'SOURCE' => 'MOODLE'
        ];

        $ch = curl_init($config->apiurl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response);

        if (!empty($result->redirect_url)) {
            return [
                'success' => true,
                'redirect_url' => $result->redirect_url
            ];
        }

        return [
            'success' => false,
            'message' => get_string('paymentrequestfailed', 'paygw_ezpay')
        ];
    }

    /**
     * Returns description of method result value for initiating payment transaction.
     *
     * @return external_single_structure
     */
    public static function initiate_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Was the payment initiated successfully?'),
            'redirect_url' => new external_value(PARAM_URL, 'URL to redirect the user to', VALUE_OPTIONAL),
            'message' => new external_value(PARAM_TEXT, 'Error message if the payment was not initiated successfully', VALUE_OPTIONAL)
        ]);
    }
}
