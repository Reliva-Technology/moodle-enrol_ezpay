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

// Force debugging on for this page
define('DEBUG', true);
define('DEBUG_DEVELOPER', true);

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/payment/gateway/ezpay/classes/redirect.php');

// Log that this file is being executed
debugging('EZPAY REDIRECT: Starting redirect.php execution', DEBUG_DEVELOPER);

// Get the required parameters
$component = required_param('component', PARAM_COMPONENT);
$paymentarea = required_param('paymentarea', PARAM_AREA);
$itemid = required_param('itemid', PARAM_INT);
$description = required_param('description', PARAM_TEXT);

// Log the parameters
debugging('EZPAY REDIRECT: Parameters - component: ' . $component . ', paymentarea: ' . $paymentarea . ', itemid: ' . $itemid . ', description: ' . $description, DEBUG_DEVELOPER);

// Get the payable object
$payable = \core_payment\helper::get_payable($component, $paymentarea, $itemid);
$amount = $payable->get_amount();
$currency = $payable->get_currency();

// Log the payment details
debugging('EZPAY REDIRECT: Payment details - amount: ' . $amount . ', currency: ' . $currency, DEBUG_DEVELOPER);

// Apply surcharge if any
$surcharge = \core_payment\helper::get_gateway_surcharge('ezpay');
$amount = \core_payment\helper::get_rounded_cost($amount, $currency, $surcharge);

// Get the payment gateway configuration
global $USER, $DB;
$config = (object) get_config('paygw_ezpay');

// Debug the API URL
debugging('EzPay API URL: ' . $config->apiurl, DEBUG_DEVELOPER);

// Create a payment record
$paymentid = \core_payment\helper::save_payment(
    $component,
    $paymentarea,
    $itemid,
    $USER->id,
    $amount,
    $currency,
    'ezpay'
);

// Generate a unique transaction ID with prefix
$transid = 'MOODLE-' . $paymentid;

// Format amount to 2 decimal places
$formattedAmount = number_format($amount, 2, '.', '');

// Get service code from config or use default
$serviceCode = !empty($config->servicecode) ? $config->servicecode : '001';

// Prepare the payment data
$data = [
    'TRANS_ID' => $transid,
    'AMOUNT' => $formattedAmount,
    'MERCHANT_CODE' => $config->merchantcode,
    'SERVICE_CODE' => $serviceCode,
    'RETURN_URL' => (new \moodle_url('/payment/gateway/ezpay/process.php'))->out(false),
    'EMAIL' => $USER->email,
    'SOURCE' => 'MOODLE',
    'PAYEE_ID' => $USER->id,
    'PAYEE_NAME' => fullname($USER),
    'PAYEE_TYPE' => 'OTHRS',
    'PAYMENT_DETAILS' => $description
];

// Debug the request data
debugging('EzPay Request Data: ' . json_encode($data), DEBUG_DEVELOPER);

// Send the request to IIUM EzPay
$ch = curl_init($config->apiurl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for testing
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Disable SSL host verification for testing
$response = curl_exec($ch);

// Check for curl errors
if (curl_errno($ch)) {
    debugging('Curl error: ' . curl_error($ch), DEBUG_DEVELOPER);
}

curl_close($ch);

// Debug the response
debugging('EzPay Response: ' . $response, DEBUG_DEVELOPER);

// Process the response
$result = json_decode($response);

// Set up the page
$PAGE->set_context(\context_system::instance());
$PAGE->set_url(new moodle_url('/payment/gateway/ezpay/redirect.php'));
$PAGE->set_title(get_string('redirectingtoezpay', 'paygw_ezpay'));
$PAGE->set_heading(get_string('redirectingtoezpay', 'paygw_ezpay'));

echo $OUTPUT->header();

// Check if we have a valid redirect URL
if (!empty($result) && !empty($result->redirect_url)) {
    $redirecturl = $result->redirect_url;
    debugging('EZPAY REDIRECT: Valid redirect URL found: ' . $redirecturl, DEBUG_DEVELOPER);
    
    // Display a loading message
    echo $OUTPUT->notification(get_string('redirectingtoezpay', 'paygw_ezpay'), 'info');
    
    // Add JavaScript to redirect the user
    echo html_writer::script(
        'console.log("Redirecting to: ' . $redirecturl . '"); ' .
        'setTimeout(function() { window.location.href = "' . $redirecturl . '"; }, 1000);'
    );
    
    // Add a fallback link in case JavaScript is disabled
    echo html_writer::div(
        html_writer::link($redirecturl, get_string('clickheretoproceed', 'paygw_ezpay')),
        'mt-3 btn btn-primary'
    );
} else {
    // Log that no redirect URL was found
    debugging('EZPAY REDIRECT: No redirect URL found in response, creating direct payment form', DEBUG_DEVELOPER);
    
    // Display a payment form instead
    echo '<div class="card">';
    echo '<div class="card-header"><h3>' . get_string('paymentdetails', 'payment') . '</h3></div>';
    echo '<div class="card-body">';
    
    // Display payment details
    echo '<div class="mb-3">';
    echo '<strong>' . get_string('paymentfor', 'payment') . ':</strong> ' . $description;
    echo '</div>';
    
    echo '<div class="mb-3">';
    echo '<strong>' . get_string('amount', 'payment') . ':</strong> ' . $formattedAmount . ' ' . $currency;
    echo '</div>';
    
    // Create a form that submits to the process.php file
    echo '<form action="' . new \moodle_url('/payment/gateway/ezpay/process.php') . '" method="post">';
    
    // Add hidden fields
    echo '<input type="hidden" name="TRANS_ID" value="' . $transid . '">';
    echo '<input type="hidden" name="STATUS" value="SUCCESS">';
    
    // Add a submit button
    echo '<button type="submit" class="btn btn-primary">' . get_string('processpayment', 'paygw_ezpay') . '</button>';
    
    echo '</form>';
    
    // Add a cancel button
    echo '<div class="mt-3">';
    echo '<a href="' . new \moodle_url('/payment/gateway/ezpay/cancel.php', ['id' => $paymentid]) . '" class="btn btn-secondary">' . get_string('cancel') . '</a>';
    echo '</div>';
    
    echo '</div>'; // card-body
    echo '</div>'; // card
}

echo $OUTPUT->footer();
