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

/**
 * The admin global settings for inserting ezpay credentials
 *
 * @package   enrol_ezpay
 * @copyright 2025 Fadli Saad <fadlisaad@gmail.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Settings header
    $settings->add(new admin_setting_heading(
        'enrol_ezpay_settings',
        get_string('pluginname', 'enrol_ezpay'),
        get_string('pluginname_desc', 'enrol_ezpay')
    ));

    // Environment setting
    $settings->add(new admin_setting_configselect(
        'enrol_ezpay/environment',
        get_string('environment', 'enrol_ezpay'),
        get_string('environment_help', 'enrol_ezpay'),
        'sandbox',
        [
            'sandbox' => get_string('environment_sandbox', 'enrol_ezpay'),
            'production' => get_string('environment_production', 'enrol_ezpay')
        ]
    ));

    // Merchant ID
    $settings->add(new admin_setting_configtext(
        'enrol_ezpay/merchant_code',
        get_string('merchant_code', 'enrol_ezpay'),
        get_string('merchant_code_help', 'enrol_ezpay'),
        '',
        PARAM_TEXT
    ));

    // Currency
    $settings->add(new admin_setting_configtext(
        'enrol_ezpay/currency',
        get_string('currency', 'enrol_ezpay'),
        get_string('currency_help', 'enrol_ezpay'),
        'MYR',
        PARAM_TEXT
    ));

    // Default enrolment duration
    $settings->add(new admin_setting_configduration(
        'enrol_ezpay/enrolperiod',
        get_string('enrolperiod', 'enrol_ezpay'),
        get_string('enrolperiod_desc', 'enrol_ezpay'),
        0
    ));

    // Default role
    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect(
            'enrol_ezpay/roleid',
            get_string('defaultrole', 'enrol_ezpay'),
            get_string('defaultrole_help', 'enrol_ezpay'),
            $student->id,
            $options
        ));
    }
}