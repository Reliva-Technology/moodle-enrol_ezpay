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
 * EZPay enrolment plugin upgrade script
 *
 * @package    enrol_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script for enrol_ezpay
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_enrol_ezpay_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025032222) {
        // Define field signature to be added to enrol_ezpay.
        $table = new xmldb_table('enrol_ezpay');
        
        // Add signature field if it doesn't exist.
        $field = new xmldb_field('signature', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'referenceurl');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add unique index on merchant_order_id.
        $index = new xmldb_index('merchant_order_id', XMLDB_INDEX_UNIQUE, ['merchant_order_id']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2025032222, 'enrol', 'ezpay');
    }

    return true;
}
