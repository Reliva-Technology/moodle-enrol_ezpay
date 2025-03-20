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
 * Debug test file for IIUM EzPay payment gateway.
 *
 * @package    paygw_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/../../../config.php');

// Set up the page
$PAGE->set_context(\context_system::instance());
$PAGE->set_url(new moodle_url('/payment/gateway/ezpay/debug_test.php'));
$PAGE->set_title('Debug Test');
$PAGE->set_heading('Debug Test');

echo $OUTPUT->header();

// Test debugging
debugging('EZPAY DEBUG TEST: This is a test debug message', DEBUG_DEVELOPER);

// Display some information
echo '<h3>Debug Test</h3>';
echo '<p>This page tests if debugging is working properly.</p>';

// Display configuration
echo '<h3>Configuration</h3>';
$config = get_config('paygw_ezpay');
echo '<pre>';
print_r($config);
echo '</pre>';

// Display PHP info
echo '<h3>PHP Info</h3>';
echo '<p>PHP Version: ' . phpversion() . '</p>';

echo $OUTPUT->footer();
