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
 * Payment cancelled page for IIUM EzPay payment gateway.
 *
 * @package    paygw_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

$id = required_param('id', PARAM_INT);

// Get the payment record
$payment = $DB->get_record('payments', ['id' => $id], '*', MUST_EXIST);

// Set up the page
$PAGE->set_context(\context_system::instance());
$PAGE->set_url(new moodle_url('/payment/gateway/ezpay/cancel.php', ['id' => $id]));
$PAGE->set_title(get_string('paymentcancelled', 'paygw_ezpay'));
$PAGE->set_heading(get_string('paymentcancelled', 'paygw_ezpay'));

echo $OUTPUT->header();

// Display cancellation message
echo $OUTPUT->notification(get_string('paymentcancelled', 'paygw_ezpay'), 'warning');

// Add a retry button that redirects back to the course enrollment page
$retryurl = new moodle_url('/enrol/index.php', ['id' => $payment->itemid]);
echo $OUTPUT->single_button($retryurl, get_string('retry', 'core'), 'get');

echo $OUTPUT->footer();
