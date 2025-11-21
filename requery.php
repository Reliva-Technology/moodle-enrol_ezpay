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

require('../../config.php');
require_once($CFG->dirroot . '/enrol/ezpay/classes/ezpay_helper.php');

$ref_no = optional_param('ref_no', '', PARAM_ALPHANUMEXT);

if (empty($ref_no)) {
    throw new moodle_exception('missingparam', 'error', '', 'ref_no');
}

// Check transaction status
$helper = new \enrol_ezpay\ezpay_helper();
$response = $helper->check_transaction($ref_no);

$courseid = $helper->get_course_id_from_transactionid($ref_no);




// Prepare status for display
$status = 'unknown';
$statusstring = '';
if (is_array($response)) {
    if (isset($response['payment_status'])) {
        switch ($response['payment_status']) {
            case '1':
                $status = 'success';
                $statusstring = get_string('status_success', 'enrol_ezpay');
                // use update_transaction_and_enrol_user
                $helper->update_transaction_and_enrol_user($ref_no, $response['payment_status'], $response['receipt_no'], $ref_no);
                break;
            case '0':
                $status = 'pending';
                $statusstring = get_string('status_pending', 'enrol_ezpay');
                break;
            default:
                $status = 'failed';
                $statusstring = get_string('status_failed', 'enrol_ezpay');
                break;
        }
    } else if (isset($response['err'])) {
        $status = 'error';
        $statusstring = $response['err'];
    }
}

// Prepare template data for Mustache
$course_url = '';
if (!empty($courseid)) {
    $course_url = (new moodle_url('/course/view.php', ['id' => $courseid]))->out(false);
}
$template_data = [
    'transactions' => get_string('transactions', 'enrol_ezpay'),
    'transaction_id_label' => get_string('transaction_id', 'enrol_ezpay'),
    'transaction_id' => s($ref_no),
    'status_label' => get_string('status', 'enrol_ezpay'),
    'status_string' => $statusstring,
    'status_class' => $status,
    'error' => ($status === 'error') ? $statusstring : '',
    'course_url' => $course_url ?: $CFG->wwwroot,
    'return_to_course' => get_string('backtocourse', 'enrol_ezpay'),
];
$PAGE->set_url('/enrol/ezpay/requery.php');
$PAGE->set_title(get_string('transactions', 'enrol_ezpay'));
$PAGE->set_heading(get_string('transactions', 'enrol_ezpay'));
echo $OUTPUT->header();
if ($status === 'success') {
    // Prepare data for ezpay_callback.mustache (reuse callback template)
    $callback_data = [
        'success' => true,
        'receipt_no' => $response['receipt_no'] ?? '',
        'transaction_id' => $ref_no,
        'course_name' => $response['course_name'] ?? '',
        'course_url' => $course_url ?: $CFG->wwwroot,
        'return_header' => get_string('payment_successful', 'enrol_ezpay'),
        'return_sub_header' => get_string('thank_you_payment', 'enrol_ezpay'),
        'logo' => $OUTPUT->image_url('logo', 'enrol_ezpay'),
        'requery_url' => $CFG->wwwroot . '/enrol/ezpay/requery.php',
        'payment_failed_message' => get_string('payment_failed_or_pending', 'enrol_ezpay'),
    ];
    echo $OUTPUT->render_from_template('enrol_ezpay/ezpay_callback', $callback_data);
} else {
    echo $OUTPUT->render_from_template('enrol_ezpay/ezpay_requery', $template_data);
}
echo $OUTPUT->footer();
