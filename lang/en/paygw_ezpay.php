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
 * Strings for component 'paygw_ezpay', language 'en'
 *
 * @package    paygw_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'IIUM EzPay';
$string['pluginname_desc'] = 'The IIUM EzPay plugin allows you to receive payments via IIUM EzPay.';
$string['gatewaydescription'] = 'IIUM EzPay is the official payment gateway for International Islamic University Malaysia. Supported payment methods including FPX, credit card and e-wallet.';
$string['gatewayname'] = 'IIUM EzPay';

// Merchant code settings
$string['merchantcode'] = 'Merchant Code';
$string['merchantcode_desc'] = 'The merchant code provided by IIUM EzPay';
$string['merchantcode_help'] = 'Enter the merchant code that was provided to you by IIUM EzPay. This code uniquely identifies your institution in the payment system.';

// API URL settings
$string['apiurl'] = 'API URL';
$string['apiurl_desc'] = 'The URL of the IIUM EzPay API';
$string['apiurl_help'] = 'The API URL is the endpoint where payment requests will be sent. The default URL is the production endpoint. Change this only if you need to use a different endpoint for testing or if instructed by IIUM EzPay support.';

$string['privacy:metadata'] = 'The IIUM EzPay plugin does not store any personal data except submitted transaction data.';

// Environment settings
$string['environment'] = 'Environment';
$string['environment_desc'] = 'Select the EzPay gateway environment to use.';
$string['environment_help'] = 'You can use the staging environment for testing purposes before moving to production.';
$string['environment_staging'] = 'Staging';
$string['environment_production'] = 'Production';

// Service code settings
$string['servicecode'] = 'Service Code';
$string['servicecode_desc'] = 'The service code provided by IIUM EzPay';
$string['servicecode_help'] = 'Enter the service code that was provided to you by IIUM EzPay. This code identifies the specific service being paid for.';

// Redirect settings
$string['useredirect'] = 'Use redirect method';
$string['useredirect_desc'] = 'If enabled, users will be redirected to the payment gateway instead of using a modal popup.';
$string['paymentrequestfailed'] = 'Payment request failed. Please try again or contact support.';
$string['redirectingtoezpay'] = 'Redirecting to IIUM EzPay payment gateway...';
$string['paymentcancelled'] = 'Payment was cancelled.';
$string['paymentsuccessful'] = 'Payment was successful.';
$string['proceedtopayment'] = 'Proceed to payment';
$string['clickheretoproceed'] = 'Click here to proceed to payment if you are not automatically redirected.';
$string['processpayment'] = 'Process Payment';
