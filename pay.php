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
 * Redirects user to the EzPay payment page
 *
 * @package   paygw_ezpay
 * @copyright 2025 Fadli Saad <fadlisaad@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_payment\helper;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/filelib.php');

require_login();
require_sesskey();

$component = required_param('component', PARAM_COMPONENT);
$paymentarea = required_param('paymentarea', PARAM_AREA);
$itemid = required_param('itemid', PARAM_INT);
$description = required_param('description', PARAM_TEXT);

// Get the configuration and cost details
$config = (object)helper::get_gateway_configuration($component, $paymentarea, $itemid, 'ezpay');
$payable = helper::get_payable($component, $paymentarea, $itemid);
$surcharge = helper::get_gateway_surcharge('ezpay');
$cost = helper::get_rounded_cost($payable->get_amount(), $payable->get_currency(), $surcharge);

// Format cost to 2 decimal places
$cost = number_format($cost, 2, '.', '');

// Save the payment record
$paymentid = helper::save_payment(
    $payable->get_account_id(),
    $component,
    $paymentarea,
    $itemid,
    $USER->id,
    $cost,
    $payable->get_currency(),
    'ezpay'
);

// Build the payment request parameters
$params = [
    'merchantcode' => $config->merchantcode,
    'amount' => $cost,
    'orderid' => $paymentid,
    'description' => $description,
    'returnurl' => (new moodle_url('/payment/gateway/ezpay/return.php'))->out(false),
    'callbackurl' => (new moodle_url('/payment/gateway/ezpay/callback.php'))->out(false)
];

// Generate signature
$signature = hash('sha256', implode('', $params) . $config->merchantcode);
$params['signature'] = $signature;

// Build the form and auto-submit it
$form = '<form action="' . $config->apiurl . '" method="post" id="ezpayform">';
foreach ($params as $key => $value) {
    $form .= '<input type="hidden" name="' . $key . '" value="' . s($value) . '">';
}
$form .= '</form>';
$form .= '<script>document.getElementById("ezpayform").submit();</script>';

// Set up the page
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/payment/gateway/ezpay/pay.php');
$PAGE->set_title(get_string('redirecting', 'paygw_ezpay'));

// Output the form
echo $OUTPUT->header();
echo $form;
echo $OUTPUT->footer();
