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
 * Listens to any callbacks from ezpay.
 *
 * @package   enrol_ezpay
 * @copyright 2025 Fadli Saad <fadlisaad@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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

$post = file_get_contents('php://input');
$request = json_decode($post);

// Get payment status from response
$paymentstatus = $request->data->payment_status ?? $request->payment_status;

if (!$paymentstatus) {
    http_response_code(503);
    throw new moodle_exception('call_error', 'enrol_ezpay');
}

// Only process successful payments
if ($paymentstatus !== ezpay_status_codes::CHECK_STATUS_SUCCESS) {
    http_response_code(503);
    throw new moodle_exception('call_error', 'enrol_ezpay');
}

$merchantorderid = required_param('merchant_order_id', PARAM_TEXT);

// Making sure that merchant order id is in the correct format.
$custom = explode('-', $merchantorderid);
$userid = $custom[1];
$courseid = $custom[2];
$instanceid = $custom[3];

$data = new stdClass();
$data->userid = (int)$userid;
$data->courseid = (int)$courseid;
$user = $DB->get_record("user", ["id" => $userid], "*", MUST_EXIST);
$course = $DB->get_record("course", ["id" => $courseid], "*", MUST_EXIST);
$context = context_course::instance($courseid, MUST_EXIST);
$PAGE->set_context($context);

// Set enrolment duration (default from Moodle).
// Only accessible if all required parameters are available.
$data->instanceid = (int)$instanceid;
$plugininstance = $DB->get_record("enrol", ["id" => $data->instanceid, "enrol" => "ezpay", "status" => 0], "*", MUST_EXIST);
$plugin = enrol_get_plugin('ezpay');
if ($plugininstance->enrolperiod) {
    $timestart = time();
    $timeend = $timestart + $plugininstance->enrolperiod;
} else {
    $timestart = 0;
    $timeend = 0;
}

// Enrol user and update database.
$plugin->enrol_user($plugininstance, $user->id, $plugininstance->roleid, $timestart, $timeend);

// Get the transaction data.
$sql = 'SELECT * FROM {enrol_ezpay}
        WHERE merchant_order_id = :merchant_order_id
        ORDER BY {enrol_ezpay}.timestamp DESC';

$params = ['merchant_order_id' => $request->data->transaction->merchant_id];
$existingdata = $DB->get_record_sql($sql, $params);

$data->id = $existingdata->id;
$data->payment_status = 'Success';
$data->pending_reason = get_string('log_callback', 'enrol_ezpay');
$data->response = json_encode($request);
$data->timeupdated = time();

$DB->update_record('enrol_ezpay', $data);

// Trigger event
$params = [
    'context' => $context,
    'courseid' => $courseid,
    'instanceid' => $instanceid,
    'userid' => $userid,
    'payment_status' => $paymentstatus,
    'merchant_order_id' => $merchantorderid
];
$event = \enrol_ezpay\event\payment_successful::create($params);
$event->trigger();

// Prepare template data
$templatedata = [
    'success' => true,
    'receipt_no' => $request->data->receipt_no,
    'payment_date' => $request->data->transaction->payment_date,
    'amount' => $request->data->transaction->amount,
    'buyer_name' => $request->data->transaction->buyer_name,
    'payment_mode' => $request->data->transaction->payment_mode,
    'buyer_bank' => $request->data->transaction->buyer_bank,
    'transaction_id' => $request->data->transaction->merchant_transaction_id,
    'course_name' => $course->fullname,
    'course_url' => (new moodle_url('/course/view.php', ['id' => $courseid]))->out(false),
    'return_header' => get_string('payment_successful', 'enrol_ezpay'),
    'return_sub_header' => get_string('thank_you_payment', 'enrol_ezpay')
];

// Output the page
$PAGE->set_url('/enrol/ezpay/callback.php');
$PAGE->set_title(get_string('payment_successful', 'enrol_ezpay'));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('enrol_ezpay/ezpay_callback', $templatedata);
echo $OUTPUT->footer();
