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
 * Session check file for IIUM EzPay payment gateway.
 *
 * @package    paygw_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Force debugging on for this page
define('DEBUG', true);
define('DEBUG_DEVELOPER', true);

require_once(__DIR__ . '/../../../config.php');

// Set up the page
$PAGE->set_context(\context_system::instance());
$PAGE->set_url(new moodle_url('/payment/gateway/ezpay/session_check.php'));
$PAGE->set_title('Session Check');
$PAGE->set_heading('Session Check');

echo $OUTPUT->header();

echo '<h3>Session Check</h3>';

// Check if the redirect URL is stored in the session
if (isset($_SESSION['ezpay_redirect_url'])) {
    echo '<div class="alert alert-success">Redirect URL found in session: ' . $_SESSION['ezpay_redirect_url'] . '</div>';
    echo '<a href="' . $_SESSION['ezpay_redirect_url'] . '" class="btn btn-primary" target="_blank">Open Redirect URL</a>';
} else {
    echo '<div class="alert alert-warning">No redirect URL found in the session.</div>';
}

// Display session information
echo '<h4>Session Information</h4>';
echo '<pre>';
echo 'Session ID: ' . session_id() . "\n";
echo 'Session Status: ' . session_status() . " (1=Disabled, 2=None, 3=Active)\n";
echo '</pre>';

// Display Moodle session variables (safely)
echo '<h4>Moodle Session Variables</h4>';
echo '<pre>';
echo 'User ID: ' . $USER->id . "\n";
echo 'Username: ' . $USER->username . "\n";
echo 'Is Logged In: ' . (isloggedin() ? 'Yes' : 'No') . "\n";
echo 'Is Guest: ' . (isguestuser() ? 'Yes' : 'No') . "\n";
echo '</pre>';

// Display server information
echo '<h4>Server Information</h4>';
echo '<pre>';
echo 'PHP Version: ' . phpversion() . "\n";
echo 'Server Software: ' . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo 'Request URI: ' . $_SERVER['REQUEST_URI'] . "\n";
echo 'HTTP Referer: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Not set') . "\n";
echo '</pre>';

// Add a link to test the redirect again
echo '<h4>Test Redirect</h4>';
echo '<p>You can use the button below to test the redirect process again:</p>';

// Create a test redirect URL
$testRedirectUrl = new moodle_url('/payment/gateway/ezpay/redirect.php', [
    'component' => 'enrol_fee',
    'paymentarea' => 'fee',
    'itemid' => 1,
    'description' => 'Test Payment'
]);

echo '<a href="' . $testRedirectUrl->out() . '" class="btn btn-primary">Test Redirect Process</a>';

echo $OUTPUT->footer();
