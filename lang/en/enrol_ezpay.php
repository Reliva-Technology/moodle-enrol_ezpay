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
 * Strings for component 'enrol_ezpay'
 *
 * @package    enrol_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// General settings
$string['ezpayaccepted'] = 'EZPay Accepted';
$string['pluginname'] = 'EZPay';
$string['pluginname_desc'] = 'The EZPay module allows you to set up paid courses. If the cost for any course is zero, then students are not asked to pay for entry. There is a site-wide cost that you set here as a default for the whole site and then a course setting that you can set for each course individually. The course cost overrides the site cost.';
$string['assignrole'] = 'Assign role';
$string['cost'] = 'Enrol cost';
$string['cost_help'] = 'If the cost is 0, then no payment is required to enrol in the course.';
$string['costerror'] = 'The enrolment cost is not numeric';
$string['costorkey'] = 'Please choose one of the following methods of enrolment.';
$string['currency'] = 'Currency';
$string['currency_help'] = 'Currency of the payment (MYR)';
$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_help'] = 'Select the default role that will be assigned to users upon enrolment via EZPay.';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during EZPay enrolments';
$string['enrolenddate'] = 'End date';
$string['enrolenddate_help'] = 'If enabled, users can be enrolled until this date only.';
$string['enrolenddaterror'] = 'Enrolment end date cannot be earlier than start date';
$string['enrolperiod'] = 'Enrolment duration';
$string['enrolperiod_desc'] = 'Default length of time that the enrolment is valid. If set to zero, the enrolment duration will be unlimited by default.';
$string['enrolperiod_help'] = 'Length of time that the enrolment is valid, starting with the moment the user is enrolled. If disabled, the enrolment duration will be unlimited.';
$string['enrolstartdate'] = 'Start date';
$string['enrolstartdate_help'] = 'If enabled, users can be enrolled from this date onward only.';
$string['expiredaction'] = 'Enrolment expiration action';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['expiry_desc'] = 'Payment expiry in hours';

// Notification settings
$string['notification'] = 'Notification settings';
$string['notification_desc'] = 'Configure who should be notified about payment events';
$string['mailadmins'] = 'Notify admin';
$string['mailadmins_help'] = 'If enabled, site administrators will receive email notifications about successful payments.';
$string['mailstudents'] = 'Notify students';
$string['mailstudents_help'] = 'If enabled, students will receive email notifications about their payment status.';
$string['mailteachers'] = 'Notify teachers';
$string['mailteachers_help'] = 'If enabled, teachers will receive email notifications about student payments.';
$string['messageprovider:ezpay_enrolment'] = 'EZPay enrolment messages';

// Status settings
$string['status'] = 'Allow EZPay enrolments';
$string['status_desc'] = 'Allow users to use EZPay to enrol into a course by default.';
$string['unenrolselfconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"?';

// Environment settings
$string['environment'] = 'Environment';
$string['environment_desc'] = 'Select the environment to use for EZPay';
$string['environment_help'] = 'You can set this to Sandbox if you want to test with sandbox environment (no real money will be charged).';
$string['environment_sandbox'] = 'Sandbox';
$string['environment_production'] = 'Production';
$string['merchant_code'] = 'Merchant Code';
$string['merchant_code_help'] = 'The merchant code provided by EZPay for your institution';
$string['merchant_key'] = 'Merchant Key';
$string['merchant_key_help'] = 'The merchant key that EZPay gave you';

// Error messages
$string['errdisabled'] = 'The EZPay enrolment plugin is disabled and does not handle payment notifications.';
$string['erripninvalid'] = 'Instant payment notification has not been verified by EZPay.';
$string['errezpayconnect'] = 'Could not connect to EZPay. Please try again later.';
$string['errezpayfailed'] = 'Payment has not been confirmed. Please contact the course administrator.';
$string['errezpayinvalid'] = 'The payment has not been validated by EZPay';
$string['erripaymuconnect'] = 'Could not connect to ezpay payment gateway.';

// Payment status
$string['payment_successful'] = 'Payment Successful';
$string['payment_started'] = 'Payment Started';
$string['payment_pending'] = 'Your payment is being processed.';
$string['payment_failed'] = 'Payment Failed';
$string['status_success'] = 'Success';
$string['status_pending'] = 'Pending';
$string['status_failed'] = 'Failed';
$string['status_cancelled'] = 'Cancelled';
$string['status_refunded'] = 'Refunded';
$string['status_chargeback'] = 'Chargeback';

// Buttons and labels
$string['sendpaymentbutton'] = 'Pay with EZPay';
$string['cost_preview'] = 'Course cost: {$a}';
$string['paymentrequired'] = 'You must make a payment to access this course.';
$string['nocost'] = 'There is no cost associated with enrolling in this course!';

// Log strings
$string['log_callback'] = 'EZPay callback with errors';
$string['log_callback_missing'] = 'EZPay callback missing data';
$string['log_callback_missing_instance'] = 'EZPay callback missing instance {$a}';
$string['log_callback_missing_user'] = 'EZPay callback missing user {$a}';
$string['log_callback_missing_course'] = 'EZPay callback missing course {$a}';
$string['log_callback_missing_context'] = 'EZPay callback missing context {$a}';
$string['log_callback_missing_plugin'] = 'EZPay callback missing plugin {$a}';

// Privacy metadata
$string['privacy:metadata:enrol_ezpay:enrol_ezpay'] = 'Information about the EZPay transactions';
$string['privacy:metadata:enrol_ezpay:enrol_ezpay:userid'] = 'The ID of the user who paid';
$string['privacy:metadata:enrol_ezpay:enrol_ezpay:courseid'] = 'The ID of the course that was paid for';
$string['privacy:metadata:enrol_ezpay:enrol_ezpay:instanceid'] = 'The ID of the enrolment instance in the course';
$string['privacy:metadata:enrol_ezpay:enrol_ezpay:merchant_order_id'] = 'The unique order ID for the payment';
$string['privacy:metadata:enrol_ezpay:enrol_ezpay:payment_status'] = 'The status of the payment';
$string['privacy:metadata:enrol_ezpay:enrol_ezpay:timeupdated'] = 'The time when the payment status was last updated';

// Capability strings
$string['enrol:config'] = 'Configure ezpay enrol instances';
$string['enrol:manage'] = 'Manage enrolled users';
$string['enrol:unenrol'] = 'Unenrol users from course';
$string['enrol:unenrolself'] = 'Unenrol self from course';
$string['ezpay:config'] = 'Configure ezpay enrol instances';
$string['ezpay:manage'] = 'Manage enrolled users';
$string['ezpay:unenrol'] = 'Unenrol users from course';
$string['ezpay:unenrolself'] = 'Unenrol self from course';

// Other strings
$string['thank_you_payment'] = 'Thank you for your payment. You have been successfully enrolled in the course.';
