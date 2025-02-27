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
 * EzPay enrolment plugin settings and presets.
 *
 * @package    enrol_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    // General settings
    $settings->add(new admin_setting_heading('enrol_ezpay_settings', 
        get_string('pluginname', 'enrol_ezpay'), 
        get_string('pluginname_desc', 'enrol_ezpay')));

    // Add instance to new courses
    $settings->add(new admin_setting_configcheckbox('enrol_ezpay/defaultenrol',
        get_string('defaultenrol', 'enrol_ezpay'),
        get_string('defaultenrol_desc', 'enrol_ezpay'),
        0));

    // Default role assignment
    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        
        $settings->add(new admin_setting_configselect('enrol_ezpay/roleid',
            get_string('defaultrole', 'enrol_ezpay'),
            get_string('defaultrole_desc', 'enrol_ezpay'),
            $student->id,
            $options));
    }

    // Enrollment duration
    $options = array(
        '0' => get_string('unlimited'),
        '86400' => get_string('numday', '', 1),
        '604800' => get_string('numweek', '', 1),
        '1209600' => get_string('numweeks', '', 2),
        '2419200' => get_string('numweeks', '', 4),
        '7257600' => get_string('nummonths', '', 3),
        '15724800' => get_string('nummonths', '', 6),
        '31449600' => get_string('numyear', '', 1),
    );
    $settings->add(new admin_setting_configselect('enrol_ezpay/enrolperiod',
        get_string('enrolperiod', 'enrol_ezpay'),
        get_string('enrolperiod_desc', 'enrol_ezpay'),
        '0',
        $options));

    // Notify before enrollment expires
    $options = array(
        0 => get_string('no'),
        1 => get_string('expirynotifyenroller', 'core_enrol'),
        2 => get_string('expirynotifyall', 'core_enrol')
    );
    $settings->add(new admin_setting_configselect('enrol_ezpay/expirynotify',
        get_string('expirynotify', 'core_enrol'),
        get_string('expirynotify_help', 'core_enrol'),
        0,
        $options));

    // Notification threshold
    $settings->add(new admin_setting_configduration('enrol_ezpay/expirythreshold',
        get_string('expirythreshold', 'core_enrol'),
        get_string('expirythreshold_help', 'core_enrol'),
        86400 * 7, // 7 days default
        86400)); // One day in seconds

    // Default cost
    $settings->add(new admin_setting_configtext('enrol_ezpay/cost',
        get_string('cost', 'enrol_ezpay'),
        get_string('cost_desc', 'enrol_ezpay'),
        '0',
        PARAM_FLOAT,
        4));

    // Default currency
    $settings->add(new admin_setting_configtext('enrol_ezpay/currency',
        get_string('currency', 'enrol_ezpay'),
        get_string('currency_desc', 'enrol_ezpay'),
        'MYR',
        PARAM_ALPHA,
        3));
}
