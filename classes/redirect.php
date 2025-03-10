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
 * Contains the redirect handler class for IIUM EzPay payment gateway.
 *
 * @package    paygw_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_ezpay;

defined('MOODLE_INTERNAL') || die();

/**
 * The redirect handler class for IIUM EzPay payment gateway.
 *
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class redirect {
    /**
     * Process the payment and redirect to IIUM EzPay.
     *
     * @param int $component The component the payment belongs to
     * @param string $paymentarea The payment area within the component
     * @param int $itemid The item ID within the payment area
     * @param string $description The payment description
     * @param float $amount The payment amount
     * @param string $currency The payment currency
     * @return string The URL to redirect to
     */
    public static function process_payment($component, $paymentarea, $itemid, $description, $amount, $currency) {
        global $USER, $DB, $CFG;
        
        // Get the payment gateway configuration
        $config = (object) get_config('paygw_ezpay');
        
        // Create a payment record
        $paymentid = \core_payment\helper::save_payment(
            $component,
            $paymentarea,
            $itemid,
            $USER->id,
            $amount,
            $currency,
            'ezpay'
        );
        
        // Generate a unique transaction ID with prefix
        $transid = 'MOODLE-' . $paymentid;
        
        // Format amount to 2 decimal places
        $formattedAmount = number_format($amount, 2, '.', '');
        
        // Get service code from config or use default
        $serviceCode = !empty($config->servicecode) ? $config->servicecode : '001';
        
        // Prepare the payment data
        $data = [
            'TRANS_ID' => $transid,
            'AMOUNT' => $formattedAmount,
            'MERCHANT_CODE' => $config->merchantcode,
            'SERVICE_CODE' => $serviceCode,
            'RETURN_URL' => (new \moodle_url('/payment/gateway/ezpay/process.php'))->out(false),
            'EMAIL' => $USER->email,
            'SOURCE' => 'MOODLE',
            'PAYEE_ID' => $USER->id,
            'PAYEE_NAME' => fullname($USER),
            'PAYEE_TYPE' => 'OTHRS',
            'PAYMENT_DETAILS' => $description
        ];
        
        // Send the request to IIUM EzPay
        $ch = curl_init($config->apiurl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response);
        
        if (!empty($result->redirect_url)) {
            return $result->redirect_url;
        }
        
        // If there's an error, redirect to the cancel page
        return new \moodle_url('/payment/gateway/ezpay/cancel.php', ['id' => $paymentid]);
    }
}
