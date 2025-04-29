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
 * Listens to any callbacks from ezpay.
 *
 * @package   enrol_ezpay
 * @copyright 2025 Fadli Saad <fadlisaad@gmail.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use enrol_ezpay\ezpay_status_codes;
use enrol_ezpay\ezpay_helper;

// Disable moodle specific debug messages and any errors in output.
define('NO_DEBUG_DISPLAY', true);

// This script does not require login.
require("../../config.php"); // phpcs:ignore
require_once("lib.php");
require_once("{$CFG->libdir}/enrollib.php");
require_once("{$CFG->libdir}/filelib.php");

// Make sure we don't timeout and don't write to session
\core_php_time_limit::raise(0);
session_write_close();

// Make sure we are enabled in the first place.
if (!enrol_is_enabled('ezpay')) {
    http_response_code(503);
    throw new moodle_exception('errdisabled', 'enrol_ezpay');
}

// Securely fetch input parameters using Moodle's optional_param() for security compliance.
$paymentstatus = optional_param('payment_status', null, PARAM_ALPHANUMEXT);
$merchantorderid = optional_param('transaction_id', null, PARAM_ALPHANUMEXT);
$refno = optional_param('ref_no', null, PARAM_ALPHANUMEXT);
$receiptno = optional_param('receipt_no', '', PARAM_ALPHANUMEXT);

// Only support GET-based (redirect) callbacks
if (!empty($paymentstatus) && !empty($merchantorderid)) {
    // --- Handle GET-based callback (user redirect after payment) ---
    // Handle requery from the form (if only ref_no is present)
    if (!empty($refno) && empty($paymentstatus)) {
        $helper = new ezpay_helper();
        $response = $helper->check_transaction($refno);
        // Map gateway response to expected keys for display
        $paymentstatus = $response['payment_status'] ?? '0';
        $merchantorderid = $refno;
        $receiptno = $response['receipt_no'] ?? '';
        // Optionally map other fields from $response as needed
    }
    // Update transaction record and enrol user using helper
    $helper = new \enrol_ezpay\ezpay_helper();
    $result = $helper->update_transaction_and_enrol_user($merchantorderid, $paymentstatus, $receiptno, $refno);
    if ($result) {
        $course_name = $result['course_name'];
        $course_url = $result['course_url'];
        $existingdata = $result['existingdata'];
    }

    // Prepare template data
    $templatedata = [
        'success' => $paymentstatus,
        'success_is_1' => ($paymentstatus == '1'),
        'receipt_no' => $receiptno,
        'transaction_id' => $merchantorderid,
        'course_name' => $course_name,
        'course_url' => $course_url,
        'return_header' => (
            $paymentstatus == '1' ? get_string('payment_successful', 'enrol_ezpay') :
            ($paymentstatus == '0' ? get_string('status_pending', 'enrol_ezpay') : get_string('status_failed', 'enrol_ezpay'))
        ),
        'return_sub_header' => (
            $paymentstatus == '1' ? get_string('thank_you_payment', 'enrol_ezpay') :
            ($paymentstatus == '0' ? get_string('payment_pending_message', 'enrol_ezpay') : get_string('payment_failed_or_pending', 'enrol_ezpay'))
        ),
        'logo' => $OUTPUT->image_url('logo', 'enrol_ezpay'),
        'requery_url' => $CFG->wwwroot . '/enrol/ezpay/requery.php',
        'payment_failed_message' => get_string('payment_failed_or_pending', 'enrol_ezpay'),
    ];
    $PAGE->set_url('/enrol/ezpay/callback.php');
    $PAGE->set_title(
    $paymentstatus == '1' ? get_string('payment_successful', 'enrol_ezpay') :
    ($paymentstatus == '0' ? get_string('payment_pending', 'enrol_ezpay') : get_string('payment_failed', 'enrol_ezpay'))
);
    $PAGE->set_heading($course_name);
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('enrol_ezpay/ezpay_callback', $templatedata);
    echo $OUTPUT->footer();
    exit;
}