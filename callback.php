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
// sample response
// http://localhost:8000/enrol/ezpay/callback.php?transaction_id=ezpay_68074ddcb201e&payment_status=1&ref_no=a1y1pRK1psBLQ76H&receipt_no=CM00025000012
// Only support GET-based (redirect) callbacks
if (!empty($_GET) && isset($_GET['payment_status']) && isset($_GET['transaction_id'])) {
    // --- Handle GET-based callback (user redirect after payment) ---
    $merchantorderid = $_GET['transaction_id'];
    $paymentstatus = $_GET['payment_status'];

    // Handle requery from the form (if only ref_no is present)
if (!empty($_GET['ref_no']) && empty($_GET['payment_status'])) {
    $helper = new ezpay_helper();
    $response = $helper->check_transaction($_GET['ref_no']);
    // Map gateway response to expected GET keys for display
    $_GET['payment_status'] = $response['payment_status'] ?? '0';
    $_GET['transaction_id'] = $_GET['ref_no'];
    $_GET['receipt_no'] = $response['receipt_no'] ?? '';
    // Optionally map other fields from $response as needed
    $merchantorderid = $_GET['transaction_id'];
    $paymentstatus = $_GET['payment_status'];
} else {
    $merchantorderid = $_GET['transaction_id'];
    $paymentstatus = $_GET['payment_status'];
}
// Get transaction record
$existingdata = $DB->get_record('enrol_ezpay', ['merchant_order_id' => $merchantorderid]);

    if ($existingdata) {

        $existingdata->payment_status = $paymentstatus == '1' ? 'Success' : 'Failed';
        $existingdata->pending_reason = get_string('log_callback', 'enrol_ezpay');
        $existingdata->response = json_encode($_GET);
        $existingdata->timeupdated = time();
        $DB->update_record('enrol_ezpay', $existingdata);

        // Enrol the user in the course if not already enrolled
        if (!empty($existingdata->userid)) {
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
        $course_name = $course->fullname;
        $course_url = (new moodle_url('/course/view.php', ['id' => $existingdata->courseid]))->out(false);
    }

    // Prepare template data
    $templatedata = [
        'success' => $paymentstatus == '1',
        'receipt_no' => $_GET['receipt_no'] ?? '',
        'transaction_id' => $_GET['ref_no'] ?? '',
        'course_name' => $course_name,
        'course_url' => $course_url,
        'return_header' => get_string('payment_successful', 'enrol_ezpay'),
        'return_sub_header' => get_string('thank_you_payment', 'enrol_ezpay'),
        'logo' => $OUTPUT->image_url('logo', 'enrol_ezpay')
    ];
    $PAGE->set_url('/enrol/ezpay/callback.php');
    $PAGE->set_title(get_string('payment_successful', 'enrol_ezpay'));
    $PAGE->set_heading($course_name);
    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('enrol_ezpay/ezpay_callback', $templatedata);
    echo $OUTPUT->footer();
    exit;
}