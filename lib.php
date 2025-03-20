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
 * Library functions for the IIUM EzPay payment gateway.
 *
 * @package    paygw_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Map gateway actions to the appropriate handler.
 *
 * @param string $action The action to map
 * @return \moodle_url The URL to handle the action
 */
function paygw_ezpay_get_payment_action_url(string $action): \moodle_url {
    // Map the action to the appropriate handler
    if ($action === 'ezpay/redirect') {
        return new \moodle_url('/payment/gateway/ezpay/redirect.php');
    }
    throw new \coding_exception('Unrecognized payment action');
}
