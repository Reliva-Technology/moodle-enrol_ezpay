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
 * Handles payment initiation for EZPay enrolment plugin
 *
 * @package    enrol_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("lib.php");
require_once($CFG->libdir.'/enrollib.php');

$courseid = required_param('id', PARAM_INT);
$instanceid = required_param('instance', PARAM_INT);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login();
// Prevent caching
\core\session\manager::write_close();

// Verify instance exists
$instance = $DB->get_record('enrol', array('id' => $instanceid, 'status' => 0), '*', MUST_EXIST);

// Verify the instance is valid
if ($instance->courseid != $course->id) {
    throw new moodle_exception('invalidinstance');
}

$plugin = enrol_get_plugin('ezpay');

// Verify cost
$cost = (float)$instance->cost;
if ($cost <= 0) {
    throw new moodle_exception('nocost', 'enrol_ezpay');
}

// Set up payment data
$merchantorderid = uniqid('ezpay_');
$returnurl = "$CFG->wwwroot/course/view.php?id=$course->id";
$callbackurl = "$CFG->wwwroot/enrol/ezpay/callback.php?merchantorderid=$merchantorderid";
// Create enrolment record
$enroldata = new stdClass();
$enroldata->courseid = $course->id;
$enroldata->userid = $USER->id;
$enroldata->instanceid = $instanceid;
$enroldata->merchant_order_id = $merchantorderid;
$enroldata->timeupdated = time();

// Store the record in database
if (!$DB->insert_record('enrol_ezpay', $enroldata)) {
    throw new moodle_exception('errorinserting', 'enrol_ezpay');
}

// Create payment link using EZPay helper
$helper = new \enrol_ezpay\ezpay_helper();

// Get payment URL
$link = null;
$error = null;

// Attempt to create the payment link
if (method_exists($helper, 'create')) {

    try {
        $link = $helper->create(
            $merchantorderid,
            $course->fullname,
            $cost,
            fullname($USER),
            $USER->phone1,
            $USER->email,
            $returnurl,
            $callbackurl
        );
        debugging('Link', var_export($link, true));
    } catch (Exception $e) {
        $error = $e->getMessage();

        
        debugging('EZPay payment creation failed: ' . $error, DEBUG_DEVELOPER);
        debugging('EZPay payment exception trace: ' . $e->getTraceAsString(), DEBUG_DEVELOPER);
        $DB->delete_records('enrol_ezpay', ['merchant_order_id' => $merchantorderid]);
    }
} else {
    $error = 'EZPay helper create() method does not exist.';
    debugging($error, DEBUG_DEVELOPER);
    $DB->delete_records('enrol_ezpay', ['merchant_order_id' => $merchantorderid]);
}

debugging('EZPay payment link: ' . var_export($link, true), DEBUG_DEVELOPER);

if ($link && !$error) {
    // Log the request
    $eventdata = [
        'context' => $context,
        'courseid' => $course->id,
        'other' => [
            'instanceid' => $instanceid,
            'merchantorderid' => $merchantorderid,
            'cost' => $cost
        ]
    ];
    $event = \enrol_ezpay\event\payment_started::create($eventdata);
    $event->trigger();

    // Redirect to payment page
    redirect($link);
} else {
    // Redirect with error message
    $errorurl = new moodle_url('/course/view.php', ['id' => $course->id]);
    redirect($errorurl, $error ? $error : 'Unknown error', null, \core\output\notification::NOTIFY_ERROR);
}

