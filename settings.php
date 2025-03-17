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
 * Settings for the EzPay payment gateway
 *
 * @package    paygw_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('paygw_ezpay_settings',
        get_string('pluginname', 'paygw_ezpay'),
        get_string('pluginname_desc', 'paygw_ezpay')));

    // Environment setting (staging/production)
    $environment_options = array(
        'staging' => get_string('environment_staging', 'paygw_ezpay'),
        'production' => get_string('environment_production', 'paygw_ezpay')
    );
    $settings->add(new admin_setting_configselect('paygw_ezpay/environment',
        get_string('environment', 'paygw_ezpay'),
        get_string('environment_desc', 'paygw_ezpay'),
        'staging',
        $environment_options));

    $settings->add(new admin_setting_configtext('paygw_ezpay/merchantcode',
        get_string('merchantcode', 'paygw_ezpay'),
        get_string('merchantcode_desc', 'paygw_ezpay'),
        '',
        PARAM_TEXT));

    $settings->add(new admin_setting_configtext('paygw_ezpay/apiurl',
        get_string('apiurl', 'paygw_ezpay'),
        get_string('apiurl_desc', 'paygw_ezpay'),
        '',
        PARAM_URL));
        
    $settings->add(new admin_setting_configtext('paygw_ezpay/servicecode',
        get_string('servicecode', 'paygw_ezpay'),
        get_string('servicecode_desc', 'paygw_ezpay'),
        '001',
        PARAM_TEXT));
}
