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
 * Process callback from IIUM EzPay payment gateway.
 *
 * @package    paygw_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/payment/gateway/ezpay/lib.php');

// Get response data - check both POST and GET as the gateway might use either
$data = empty($_POST) ? $_GET : $_POST;

// Log the received data for debugging
debugging('EZPAY PROCESS: Callback received with data: ' . json_encode($data), DEBUG_DEVELOPER);

// Check if we have transaction data
if (!empty($data['TRANS_ID'])) {
    // Extract the payment ID from the transaction ID (remove 'MOODLE-' prefix)
    $transid = $data['TRANS_ID'];
    $paymentid = preg_replace('/^MOODLE-/', '', $transid);
    
    debugging('EZPAY PROCESS: Processing transaction ID: ' . $transid . ', Payment ID: ' . $paymentid, DEBUG_DEVELOPER);
    
    // Get the payment record
    global $DB;
    $payment = $DB->get_record('payments', ['id' => $paymentid], '*');
    
    if ($payment) {
        debugging('EZPAY PROCESS: Payment record found for ID: ' . $paymentid, DEBUG_DEVELOPER);
        
        // Process the payment response
        $status = isset($data['STATUS']) ? $data['STATUS'] : '';
        
        debugging('EZPAY PROCESS: Payment status: ' . $status, DEBUG_DEVELOPER);
        
        // Check if payment was successful (adjust based on EzPay's success status code)
        if ($status == 'SUCCESS' || $status == '00' || $status == 'PAID' || empty($status)) {
            debugging('EZPAY PROCESS: Payment successful, delivering order', DEBUG_DEVELOPER);
            
            // Mark payment as successful
            \core_payment\helper::deliver_order($payment->component, $payment->paymentarea, $payment->itemid, $payment->userid, $payment->amount);
            
            // Redirect to success page
            redirect(new \moodle_url('/payment/gateway/ezpay/success.php', ['id' => $payment->id]));
        } else {
            debugging('EZPAY PROCESS: Payment failed with status: ' . $status, DEBUG_DEVELOPER);
            
            // Payment failed
            redirect(new \moodle_url('/payment/gateway/ezpay/cancel.php', ['id' => $payment->id]));
        }
    } else {
        debugging('EZPAY PROCESS: Payment record not found for ID: ' . $paymentid, DEBUG_DEVELOPER);
        
        // Payment record not found
        echo 'Error: Payment record not found.';
    }
} else {
    debugging('EZPAY PROCESS: No transaction ID found in callback data', DEBUG_DEVELOPER);
    
    // No transaction ID
    echo 'Error: No transaction ID provided.';
}
