<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace enrol_ezpay\task;

use core\task\scheduled_task;

class requery_payments_task extends scheduled_task {
    public function get_name() {
        return get_string('requery_payments_task', 'enrol_ezpay');
    }

    public function execute() {
        global $DB;
        require_once(__DIR__ . '/../ezpay_helper.php');
        $helper = new \enrol_ezpay\ezpay_helper();
        // Find all non-successful transactions
        $sql = "SELECT * FROM {enrol_ezpay} WHERE payment_status IS NULL OR payment_status != ?";
        $records = $DB->get_records_sql($sql, ['Success']);
        foreach ($records as $record) {
            $result = $helper->check_transaction($record->merchant_order_id);
            // Optionally: update status if changed
            if (is_array($result) && isset($result['payment_status'])) {
                $helper->update_transaction_and_enrol_user(
                    $record->merchant_order_id,
                    $result['payment_status'],
                    $result['receipt_no'] ?? '',
                    $record->merchant_order_id
                );
            }
        }
    }
}
