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

        // Use native PHP cURL to send a true multipart/form-data request as required by the gateway
        $url = $baseurl . $endpoint;
        $fields = $header['body'];
        $multipart = [];
        foreach ($fields as $key => $value) {
            $multipart[] = [
                'name' => $key,
                'contents' => $value
            ];
        }

        $ch = curl_init();
        $postfields = [];
        foreach ($multipart as $part) {
            $postfields[$part['name']] = $part['contents'];
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // Do not set Content-Type header manually

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($error) {
            return ['err' => $error];
        }
        // Build the response array similar to the sample response
        // The final payment URL after redirects is already in $info['url'] (set by cURL)
        return $info;

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
            'RETURN_URL' => $CFG->wwwroot . '/enrol/ezpay/callback.php?courseid=' . $courseid,
            'CANCEL_URL' => $cancelurl,
            'EMAIL' => $email,
            'SOURCE' => 'web',
            'PAYEE_ID' => $phone == null ? '0000000000' : $phone,
            'PAYEE_NAME' => $name,
            'PAYEE_TYPE' => 'OTHRS',
            'PAYMENT_DETAILS' => $productname
        ];

        $response = $this->send('/payment/request', $body);
        if (is_array($response) && isset($response['url'])) {
            return $response['url'];
        }
        throw new \moodle_exception('errezpayconnect', 'enrol_ezpay', '', 'Failed to get payment URL from gateway response.');
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
