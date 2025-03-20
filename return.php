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
 * Handle the return from EzPay payment gateway
 *
 * @package   paygw_ezpay
 * @copyright 2025 Fadli Saad <fadlisaad@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_payment\helper;

require_once(__DIR__ . '/../../../config.php');

$orderid = required_param('orderid', PARAM_INT);
$status = required_param('status', PARAM_TEXT);

// Get the payment record
$payment = $DB->get_record('payments', ['id' => $orderid], '*', MUST_EXIST);

// Get the success URL
$url = helper::get_success_url($payment->component, $payment->paymentarea, $payment->itemid);

// Check payment status
if ($status === 'success') {
    redirect($url, get_string('paymentsuccessful', 'paygw_ezpay'), null, \core\output\notification::NOTIFY_SUCCESS);
} else {
    redirect($url, get_string('paymentcancelled', 'paygw_ezpay'), null, \core\output\notification::NOTIFY_ERROR);
}
