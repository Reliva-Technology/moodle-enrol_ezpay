<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Stores all the functions needed to run the plugin for better readability
 *
 * @package   enrol_ezpay
 * @copyright 2025 Fadli Saad <fadlisaad@gmail.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_ezpay;

use Exception;

/**
 * Stores all reusable functions here.
 *
 * @author 2025 Fadli Saad <fadlisaad@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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

    public function send($endpoint, $body, $method = 'POST') {
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

        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();
        $options = [
            'CURLOPT_FOLLOWLOCATION' => true,
            'CURLOPT_MAXREDIRS' => 10,
            'CURLOPT_TIMEOUT' => 0,
            'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
            'CURLOPT_SSL_VERIFYPEER' => false,
            'CURLOPT_SSL_VERIFYHOST' => false,
        ];


        if ($method == 'GET') {
            $response = $curl->get($url, $fields, $options);
        } else {
            $response = $curl->post($url, $fields, $options);
        }
    
        $error = $curl->get_errno() ? $curl->error : null;

        // $info = $curl->get_info();
        if ($error) {
            return ['err' => $error];
        }
        // Build the response array similar to the sample response
        // The final payment URL after redirects is already in $info['url'] (set by cURL)

        return $response;

    }



    public function send_info($endpoint, $body, $method = 'POST') {
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

        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();
        $options = [
            'CURLOPT_FOLLOWLOCATION' => true,
            'CURLOPT_MAXREDIRS' => 10,
            'CURLOPT_TIMEOUT' => 0,
            'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
            'CURLOPT_SSL_VERIFYPEER' => false,
            'CURLOPT_SSL_VERIFYHOST' => false,
        ];


        if ($method == 'GET') {
            $response = $curl->get($url, $fields, $options);
        } else {
            $response = $curl->post($url, $fields, $options);
        }
      
        $error = $curl->get_errno() ? $curl->error : null;

        // $info = $curl->get_info();
        if ($error) {
            return ['err' => $error];
        }
        // Build the response array similar to the sample response
        // The final payment URL after redirects is already in $info['url'] (set by cURL)

        return $curl->get_info();

    }

    // public function get_trans_status($transactionid, $merchant_code) {
    //     global $CFG;
    //     require_once($CFG->libdir . '/filelib.php');

    //     $header = $this->header($body);
    //     $baseurl = $header['url'];

        
        

    // }

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

        $response = $this->send_info('/payment/request', $body, 'POST');


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
       
        // print_r($transactionid, $this->merchant_code);

        return $this->send('/api/payment/requery/'.$transactionid. "/".$this->merchant_code,[] ,  method: 'GET');
    }

    public function get_course_id_from_transactionid($transactionid) {
        global $DB;
        $record = $DB->get_record('enrol_ezpay', ['merchant_order_id' => $transactionid]);
        return $record->courseid;
    }

    public function log_request($eventarray) {
        $event = \enrol_ezpay\event\ezpay_request_log::create($eventarray);
        $event->trigger();
    }

    /**
     * Update transaction record and enrol user if needed.
     *
     * @param string $merchantorderid
     * @param string $paymentstatus
     * @param string $receiptno
     * @param string $refno
     * @return array|null [course_name, course_url, existingdata] or null if not found
     */
    public function update_transaction_and_enrol_user($merchantorderid, $paymentstatus, $receiptno, $refno) {
        global $DB, $CFG;
        $existingdata = $DB->get_record('enrol_ezpay', ['merchant_order_id' => $merchantorderid]);
        $course_name = '';
        $course_url = '';
        if ($existingdata) {
            $existingdata->payment_status = $paymentstatus == '1' ? 'Success' : 'Failed';
            $existingdata->pending_reason = get_string('log_callback', 'enrol_ezpay');
            $existingdata->response = json_encode([
                'payment_status' => $paymentstatus,
                'transaction_id' => $merchantorderid,
                'ref_no' => $refno,
                'receipt_no' => $receiptno
            ]);
            $existingdata->timeupdated = time();
            $DB->update_record('enrol_ezpay', $existingdata);

            // Enrol the user in the course if not already enrolled and payment status is success
            if (!empty($existingdata->userid) && $paymentstatus == '1') {
                $userid = $existingdata->userid;
                $enrol = enrol_get_plugin('ezpay');
                $instances = enrol_get_instances($existingdata->courseid, true);
                $instance = null;
                foreach ($instances as $i) {
                    if ($i->enrol === 'ezpay') {
                        $instance = $i;
                        break;
                    }
                }
                if ($enrol && $instance && $userid) {
                    $enrol->enrol_user($instance, $userid, $instance->roleid, time());
                }
            }
            // get course detail
            $course = $DB->get_record('course', ['id' => $existingdata->courseid]);
            if ($course) {
                $course_name = $course->fullname;
                $course_url = (new \moodle_url('/course/view.php', ['id' => $existingdata->courseid]))->out(false);
            }
        }
        return $existingdata ? [
            'course_name' => $course_name,
            'course_url' => $course_url,
            'existingdata' => $existingdata
        ] : null;
    }
}

