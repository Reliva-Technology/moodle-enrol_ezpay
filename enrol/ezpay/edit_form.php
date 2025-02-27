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
 * Adds new instance of enrol_ezpay to specified course
 * or edits current instance.
 *
 * @package    enrol_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/course/lib.php');

/**
 * EzPay enrollment settings form
 */
class enrol_ezpay_edit_form extends moodleform {

    /**
     * Form definition
     */
    public function definition() {
        global $DB, $CFG;

        $mform = $this->_form;
        $instance = $this->_customdata;
        $plugin = enrol_get_plugin('ezpay');

        $course = $DB->get_record('course', array('id' => $instance->courseid));

        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_ezpay'));

        // Status
        $options = array(
            ENROL_INSTANCE_ENABLED  => get_string('yes'),
            ENROL_INSTANCE_DISABLED => get_string('no')
        );
        $mform->addElement('select', 'status', get_string('status', 'enrol_ezpay'), $options);
        $mform->addHelpButton('status', 'status', 'enrol_ezpay');
        $mform->setDefault('status', $plugin->get_config('status'));

        // Cost
        $mform->addElement('text', 'cost', get_string('cost', 'enrol_ezpay'), array('size' => 4));
        $mform->setType('cost', PARAM_RAW);
        $mform->setDefault('cost', format_float($plugin->get_config('cost'), 2, true));

        // Currency
        $mform->addElement('text', 'currency', get_string('currency', 'enrol_ezpay'), array('size' => 3));
        $mform->setType('currency', PARAM_ALPHA);
        $mform->setDefault('currency', $plugin->get_config('currency'));

        // Role assignment
        $roles = get_assignable_roles(context_course::instance($course->id));
        $roles[0] = get_string('none');
        $mform->addElement('select', 'roleid', get_string('assignrole', 'enrol_ezpay'), $roles);
        $mform->setDefault('roleid', $plugin->get_config('roleid'));

        // Enrollment period
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
        $mform->addElement('select', 'enrolperiod', get_string('enrolperiod', 'enrol_ezpay'), $options);
        $mform->setDefault('enrolperiod', $plugin->get_config('enrolperiod'));

        // Enrollment start date
        $mform->addElement('date_time_selector', 'enrolstartdate', get_string('enrolstartdate', 'enrol_ezpay'), array('optional' => true));
        $mform->addHelpButton('enrolstartdate', 'enrolstartdate', 'enrol_ezpay');

        // Enrollment end date
        $mform->addElement('date_time_selector', 'enrolenddate', get_string('enrolenddate', 'enrol_ezpay'), array('optional' => true));
        $mform->addHelpButton('enrolenddate', 'enrolenddate', 'enrol_ezpay');

        // Expiry notification
        $options = array(
            0 => get_string('no'),
            1 => get_string('expirynotifyenroller', 'core_enrol'),
            2 => get_string('expirynotifyall', 'core_enrol')
        );
        $mform->addElement('select', 'expirynotify', get_string('expirynotify', 'core_enrol'), $options);
        $mform->addHelpButton('expirynotify', 'expirynotify', 'core_enrol');
        $mform->setDefault('expirynotify', $plugin->get_config('expirynotify'));

        // Expiry notification threshold
        $duration = array();
        $duration[86400] = get_string('numday', '', 1);
        $duration[172800] = get_string('numdays', '', 2);
        $duration[259200] = get_string('numdays', '', 3);
        $duration[432000] = get_string('numdays', '', 5);
        $duration[604800] = get_string('numweek', '', 1);
        $duration[1209600] = get_string('numweeks', '', 2);
        $duration[2419200] = get_string('numweeks', '', 4);
        $mform->addElement('select', 'expirythreshold', get_string('expirythreshold', 'core_enrol'), $duration);
        $mform->addHelpButton('expirythreshold', 'expirythreshold', 'core_enrol');
        $mform->disabledIf('expirythreshold', 'expirynotify', 'eq', 0);
        $mform->setDefault('expirythreshold', $plugin->get_config('expirythreshold'));

        // Add standard buttons
        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

        $this->set_data($instance);
    }

    /**
     * Form validation
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Make sure cost is valid
        if (!empty($data['cost']) && !is_numeric($data['cost'])) {
            $errors['cost'] = get_string('costerror', 'enrol_ezpay');
        }

        // Make sure the enrollment dates are valid
        if ($data['enrolstartdate'] && $data['enrolenddate']) {
            if ($data['enrolstartdate'] >= $data['enrolenddate']) {
                $errors['enrolenddate'] = get_string('enrolenddaterror', 'enrol_ezpay');
            }
        }

        return $errors;
    }
}
