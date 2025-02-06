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
 * Process IIUM EzPay payment response.
 *
 * @package    paygw_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/payment/gateway/ezpay/lib.php');

$data = $_POST;

// Get the payment record.
$paymentid = $data['TRANS_ID'];
$payment = $DB->get_record('payments', ['id' => $paymentid], '*', MUST_EXIST);

// Process the payment response.
if (!empty($data)) {
    // Add your payment verification logic here.
    // You should verify the payment status and other necessary checks.
    
    $paymentrecord = new stdClass();
    $paymentrecord->payment_id = $payment->id;
    $paymentrecord->payment_reference = $data['TRANS_ID'];
    $paymentrecord->status = 'success'; // Update based on actual payment status
    
    // Record the payment in Moodle.
    \core_payment\helper::save_payment($paymentrecord);
    
    // Deliver the payment completion event.
    \core_payment\helper::deliver_order($payment->component, $payment->paymentarea, $payment->itemid, $payment->userid, $payment->amount);
    
    // Redirect to success page.
    redirect(new moodle_url('/payment/gateway/ezpay/success.php', ['id' => $payment->id]));
} else {
    // Payment failed or was cancelled.
    redirect(new moodle_url('/payment/gateway/ezpay/cancel.php', ['id' => $payment->id]));
}
