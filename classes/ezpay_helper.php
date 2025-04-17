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
 * Stores all the functions needed to run the plugin for better readability
 *
 * @package   enrol_ezpay
 * @copyright 2025 Fadli Saad <fadlisaad@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_ezpay;

use Exception;

/**
 * Stores all reusable functions here.
 *
 * @author 2025 Fadli Saad <fadlisaad@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ezpay_helper {

    private $merchant_code;

    public function __construct() {
        $this->merchant_code = get_config('enrol_ezpay', 'merchant_code');
    }

    public function header($body) {
        $environment = get_config('enrol_ezpay', 'environment');

        if ($environment == 'sandbox') {
            $url = 'https://ezypay-stg.iium.edu.my';
        } else {
            $url = 'https://ezpay.iium.edu.my';
        }

        return [
            'body' => $body,
            'url' => $url
        ];
    }

    public function send($endpoint, $body) {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $header = $this->header($body);
        $baseurl = $header['url'];

        $curl = new \curl();
        $options = [
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_FOLLOWLOCATION' => true,
            'CURLOPT_MAXREDIRS' => 10,
            'CURLOPT_TIMEOUT' => 0,
            'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
            'CURLOPT_CUSTOMREQUEST' => 'POST',
            'CURLOPT_POSTFIELDS' => $header['body'],
            'CURLOPT_HTTPHEADER' => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ];

        debugging('POST body: ' . var_export($header['body'], true), DEBUG_DEVELOPER);
        // Send as form-data, not JSON
        $response = $curl->post($baseurl . $endpoint, $header['body'], $options);
        debugging('EZPay raw response: ' . var_export($response, true), DEBUG_DEVELOPER);
        if ($curl->error) {
            debugging('cURL error: ' . $curl->error, DEBUG_DEVELOPER);
            return ['err' => $curl->error];
        } else {
            // Try to decode JSON, but if not, just return raw
            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Not JSON, return raw response
                return ['err' => $response];
            }
            // If the decoded data contains an error, return it
            if (isset($data['error'])) {
                return [
                    'err' => $data['error'],
                    'code' => isset($data['code']) ? $data['code'] : null,
                    'status' => isset($data['status']) ? $data['status'] : null
                ];
            } elseif (isset($data['message']) && (isset($data['status']) && strtolower($data['status']) === 'error')) {
                return [
                    'err' => $data['message'],
                    'code' => isset($data['code']) ? $data['code'] : null,
                    'status' => $data['status']
                ];
            } elseif (isset($data['message'])) {
                return ['err' => $data['message']];
            } elseif (isset($data['status']) && strtolower($data['status']) === 'error') {
                return ['err' => isset($data['description']) ? $data['description'] : $data['status']];
            }
            // Otherwise, return the data as is
            return ['res' => $data];
        }
    }

    /**
     * Create a payment request
     *
     * @param string $merchantorderid Unique order ID
     * @param string $productname Name of the product/course
     * @param float $price Price of the product
     * @param string $name Customer name
     * @param string $phone Customer phone
     * @param string $email Customer email
     * @param string $returnurl Success return URL
     * @param string $callbackurl Callback URL for payment notification
     * @return string Payment URL or error message
     * @throws \moodle_exception if payment creation fails
     */
    public function create($merchantorderid, $productname, $price, $name, $phone, $email, $returnurl, $callbackurl) {
        global $CFG;
        
        // Get course ID from return URL
        $courseid = substr(strrchr($returnurl, '='), 1);
        $cancelurl = "$CFG->wwwroot/course/view.php?id=$courseid";

        // Ensure price is a float
        $price = (float) $price;

        $body = [
            'TRANS_ID' => $merchantorderid,
            'CURRENCY' => 'MYR',
            'AMOUNT' => number_format($price, 2, '.', ''),
            'MERCHANT_CODE' => $this->merchant_code,
            'SERVICE_CODE' => '001',
            'RETURN_URL' => $returnurl,
            'CANCEL_URL' => $cancelurl,
            'EMAIL' => $email,
            'SOURCE' => 'web',
            'PAYEE_ID' => $phone == null ? '0000000000' : $phone,
            'PAYEE_NAME' => $name,
            'PAYEE_TYPE' => 'OTHRS',
            'PAYMENT_DETAILS' => $productname
        ];

        $response = $this->send('/payment/request', $body);
        
        if (isset($response['err'])) {
            throw new \moodle_exception('errezpayconnect', 'enrol_ezpay', '', $response['err']);
        }

        if (isset($response['res']['PAYMENT_URL'])) {
            return $response['res']['PAYMENT_URL'];
        }

        throw new \moodle_exception('errezpayconnect', 'enrol_ezpay');
    }

    /**
     * Check transaction status
     *
     * @param string $transactionid Transaction ID to check
     * @param string|null $account Optional account reference
     * @return array Response from EZPay
     */
    public function check_transaction($transactionid, $account = null) {
        $body = [
            'TRANS_ID' => $transactionid,
            'MERCHANT_CODE' => $this->merchant_code,
            'SERVICE_CODE' => '001'
        ];

        return $this->send('/payment/requery', $body);
    }

    public function log_request($eventarray) {
        $event = \enrol_ezpay\event\ezpay_request_log::create($eventarray);
        $event->trigger();
    }
}
