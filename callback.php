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
 * Handle the callback from EzPay payment gateway
 *
 * @package   paygw_ezpay
 * @copyright 2025 Fadli Saad <fadlisaad@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_payment\helper;

require_once(__DIR__ . '/../../../config.php');

// Get the raw POST data
$data = file_get_contents('php://input');
$notification = json_decode($data);

if (!$notification) {
    http_response_code(400);
    die('Invalid notification data');
}

// Required parameters
$orderid = $notification->orderid;
$status = $notification->status;
$amount = $notification->amount;
$signature = $notification->signature;

// Get the payment record
$payment = $DB->get_record('payments', ['id' => $orderid], '*', MUST_EXIST);

// Get gateway configuration
$config = (object)helper::get_gateway_configuration($payment->component, $payment->paymentarea, $payment->itemid, 'ezpay');

// Verify signature
$params = [
    'orderid' => $orderid,
    'status' => $status,
    'amount' => $amount
];
$expectedSignature = hash('sha256', implode('', $params) . $config->merchantcode);

if ($signature !== $expectedSignature) {
    http_response_code(400);
    die('Invalid signature');
}

// Process the payment
if ($status === 'success') {
    // Deliver the order
    $paymentid = $payment->id;
    $userid = $payment->userid;
    
    try {
        helper::deliver_order($payment->component, $payment->paymentarea, $payment->itemid, $paymentid, $userid);
        http_response_code(200);
        die('OK');
    } catch (Exception $e) {
        debugging('Exception while processing payment: ' . $e->getMessage(), DEBUG_DEVELOPER);
        http_response_code(500);
        die('Internal error');
    }
} else {
    http_response_code(200);
    die('Payment not successful');
}
