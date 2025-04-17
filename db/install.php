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
 * Installation script for enrol_ezpay
 *
 * @package    enrol_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Custom installation procedure
 */
function xmldb_enrol_ezpay_install() {
    global $CFG, $DB;

    // Create the table if it doesn't exist
    $dbman = $DB->get_manager();

    // Define table enrol_ezpay
    $table = new xmldb_table('enrol_ezpay');

    // Adding fields
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('instanceid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('merchant_order_id', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('receiver_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('receiver_email', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('response', XMLDB_TYPE_TEXT, null, null, null, null, null);
    $table->add_field('payment_status', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('pending_reason', XMLDB_TYPE_CHAR, '255', null, null, null, null);
    $table->add_field('expiryperiod', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
    $table->add_field('timeupdated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

    // Adding keys
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
    $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
    $table->add_key('instanceid', XMLDB_KEY_FOREIGN, array('instanceid'), 'enrol', array('id'));

    // Adding indexes
    $table->add_index('merchant_order_id', XMLDB_INDEX_UNIQUE, array('merchant_order_id'));

    // Create the table
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
}
