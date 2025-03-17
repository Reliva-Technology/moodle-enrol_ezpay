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
 * Database interactions for the EzPay enrollment plugin.
 *
 * @package    enrol_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Save payment transaction record
 *
 * @param int $instanceid Enrollment instance ID
 * @param string $transactionid EzPay transaction ID
 * @param string $status Payment status
 * @return int|bool The ID of the new record, or false if the record could not be inserted
 */
function enrol_ezpay_save_transaction($instanceid, $transactionid, $status) {
    global $DB;
    
    $record = new stdClass();
    $record->instance_id = $instanceid;
    $record->transaction_id = $transactionid;
    $record->payment_status = $status;
    $record->timecreated = time();
    
    return $DB->insert_record('enrol_ezpay', $record);
}

/**
 * Get payment transaction record
 *
 * @param string $transactionid EzPay transaction ID
 * @return stdClass|false The payment record or false if not found
 */
function enrol_ezpay_get_transaction($transactionid) {
    global $DB;
    
    return $DB->get_record('enrol_ezpay', array('transaction_id' => $transactionid));
}

/**
 * Update payment transaction status
 *
 * @param string $transactionid EzPay transaction ID
 * @param string $status New payment status
 * @return bool True if the record was updated
 */
function enrol_ezpay_update_transaction_status($transactionid, $status) {
    global $DB;
    
    $transaction = enrol_ezpay_get_transaction($transactionid);
    
    if ($transaction) {
        $transaction->payment_status = $status;
        return $DB->update_record('enrol_ezpay', $transaction);
    }
    
    return false;
}
