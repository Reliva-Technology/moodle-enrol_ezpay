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
 * Redirects to IIUM EzPay payment gateway.
 *
 * @package    paygw_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/payment/gateway/ezpay/classes/redirect.php');

// Get the required parameters
$component = required_param('component', PARAM_COMPONENT);
$paymentarea = required_param('paymentarea', PARAM_AREA);
$itemid = required_param('itemid', PARAM_INT);
$description = required_param('description', PARAM_TEXT);

// Get the payable object
$payable = \core_payment\helper::get_payable($component, $paymentarea, $itemid);
$amount = $payable->get_amount();
$currency = $payable->get_currency();

// Apply surcharge if any
$surcharge = \core_payment\helper::get_gateway_surcharge('ezpay');
$amount = \core_payment\helper::get_rounded_cost($amount, $currency, $surcharge);

// Set up the page
$PAGE->set_context(\context_system::instance());
$PAGE->set_url(new moodle_url('/payment/gateway/ezpay/redirect.php'));
$PAGE->set_title(get_string('redirectingtoezpay', 'paygw_ezpay'));
$PAGE->set_heading(get_string('redirectingtoezpay', 'paygw_ezpay'));

echo $OUTPUT->header();

// Display a loading message
echo $OUTPUT->notification(get_string('redirectingtoezpay', 'paygw_ezpay'), 'info');

// Get the redirect URL
$redirecturl = \paygw_ezpay\redirect::process_payment(
    $component,
    $paymentarea,
    $itemid,
    $description,
    $amount,
    $currency
);

// Add JavaScript to redirect the user
echo html_writer::script(
    'window.location.href = "' . $redirecturl . '";'
);

echo $OUTPUT->footer();
