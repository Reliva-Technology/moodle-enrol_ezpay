<?php

class enrol_ezpay_plugin extends enrol_plugin {
    /**
     * Returns the name of this enrollment plugin
     * @param stdClass $instance
     * @return string
     */
    public function get_instance_name($instance) {
        global $DB;
        
        if (empty($instance->name)) {
            $enrol = $this->get_name();
            $course = $DB->get_record('course', array('id' => $instance->courseid));
            $instance->name = get_string('pluginname', 'enrol_ezpay') . ' (' . format_string($course->shortname) . ')';
        }
        
        return format_string($instance->name);
    }

    /**
     * Returns plugin defaults for new instances.
     * @return array
     */
    public function get_instance_defaults() {
        $defaults = array();
        $defaults['status'] = ENROL_INSTANCE_ENABLED;
        $defaults['roleid'] = $this->get_config('roleid', 5); // Default to student role (5)
        $defaults['enrolperiod'] = $this->get_config('enrolperiod', 0); // Default to unlimited
        $defaults['expirynotify'] = $this->get_config('expirynotify', 0);
        $defaults['expirythreshold'] = $this->get_config('expirythreshold', 86400 * 7); // 7 days default
        $defaults['cost'] = $this->get_config('cost', 0);
        $defaults['currency'] = $this->get_config('currency', 'MYR');
        
        return $defaults;
    }

    /**
     * Add new instance of enrol plugin with default settings.
     * @param stdClass $course
     * @return int id of new instance
     */
    public function add_default_instance($course) {
        $fields = $this->get_instance_defaults();
        $fields['courseid'] = $course->id;
        
        return $this->add_instance($course, $fields);
    }

    /**
     * Add new instance of EzPay enrollment plugin.
     * @param stdClass $course
     * @param array $fields instance fields
     * @return int id of new instance
     */
    public function add_instance($course, array $fields = null) {
        if ($fields && !empty($fields['cost'])) {
            $fields['cost'] = unformat_float($fields['cost']);
        }
        
        return parent::add_instance($course, $fields);
    }

    /**
     * Enrol a user into a course
     * @param stdClass $instance enrollment instance
     * @param int $userid user id
     * @param int $roleid role id
     * @param int $timestart start time
     * @param int $timeend end time
     * @param int $status enrollment status
     */
    public function enrol_user(stdClass $instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = null, $recovergrades = null) {
        // Call parent method to handle the actual enrollment
        parent::enrol_user($instance, $userid, $roleid, $timestart, $timeend, $status, $recovergrades);
    }

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     * @return string html text, usually a form in a text box
     */
    public function enrol_page_hook(stdClass $instance) {
        global $OUTPUT, $USER, $CFG;
        
        // Include necessary files
        require_once("$CFG->dirroot/enrol/ezpay/locallib.php");
        
        $enrolstatus = $this->can_self_enrol($instance);
        
        if (true === $enrolstatus) {
            // Show payment button or form
            $button = new enrol_ezpay_payment_form($instance, $USER->id);
            return $OUTPUT->box($button->render());
        } else {
            return $OUTPUT->notification($enrolstatus, 'error');
        }
    }
    
    /**
     * Checks if user can self enrol.
     *
     * @param stdClass $instance enrolment instance
     * @return bool|string true if successful, else error message
     */
    public function can_self_enrol(stdClass $instance) {
        global $DB, $USER, $CFG;

        if ($instance->status != ENROL_INSTANCE_ENABLED) {
            return get_string('canntenrol', 'enrol_self');
        }

        if ($instance->enrolstartdate != 0 && $instance->enrolstartdate > time()) {
            return get_string('canntenrolearly', 'enrol_self', userdate($instance->enrolstartdate));
        }

        if ($instance->enrolenddate != 0 && $instance->enrolenddate < time()) {
            return get_string('canntenrollate', 'enrol_self', userdate($instance->enrolenddate));
        }

        if (!$instance->customint6) {
            // New enrols not allowed.
            return get_string('canntenrol', 'enrol_self');
        }

        if ($DB->record_exists('user_enrolments', array('userid' => $USER->id, 'enrolid' => $instance->id))) {
            return get_string('canntenrol', 'enrol_self');
        }

        return true;
    }
    
    /**
     * Return an array of valid options for the status.
     *
     * @return array
     */
    protected function get_status_options() {
        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        return $options;
    }
    
    /**
     * Return an array of valid options for the roleid.
     *
     * @param stdClass $instance
     * @param context $context
     * @return array
     */
    protected function get_roleid_options($instance, $context) {
        if ($instance->id) {
            $roles = get_default_enrol_roles($context, $instance->roleid);
        } else {
            $roles = get_default_enrol_roles($context, $this->get_config('roleid'));
        }
        return $roles;
    }
    
    /**
     * Add elements to the edit instance form.
     *
     * @param stdClass $instance
     * @param MoodleQuickForm $mform
     * @param context $context
     * @return bool
     */
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
        global $CFG, $DB;

        // Status
        $options = $this->get_status_options();
        $mform->addElement('select', 'status', get_string('status', 'enrol_ezpay'), $options);
        $mform->addHelpButton('status', 'status', 'enrol_ezpay');

        // Cost
        $mform->addElement('text', 'cost', get_string('cost', 'enrol_ezpay'), array('size' => 4));
        $mform->setType('cost', PARAM_RAW);
        $mform->addHelpButton('cost', 'cost', 'enrol_ezpay');

        // Currency
        $mform->addElement('text', 'currency', get_string('currency', 'enrol_ezpay'), array('size' => 3));
        $mform->setType('currency', PARAM_ALPHA);
        $mform->addHelpButton('currency', 'currency', 'enrol_ezpay');

        // Role assignment
        $roles = $this->get_roleid_options($instance, $context);
        $mform->addElement('select', 'roleid', get_string('assignrole', 'enrol_ezpay'), $roles);
        $mform->addHelpButton('roleid', 'assignrole', 'enrol_ezpay');

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
        $mform->addHelpButton('enrolperiod', 'enrolperiod', 'enrol_ezpay');

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

        return true;
    }
    
    /**
     * Perform custom validation of the data used to edit the instance.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @param object $instance The instance loaded from the DB
     * @param context $context The context of the instance we are editing
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK.
     */
    public function edit_instance_validation($data, $files, $instance, $context) {
        $errors = array();

        // Make sure cost is valid
        if (!empty($data['cost']) && !is_numeric($data['cost'])) {
            $errors['cost'] = get_string('costerror', 'enrol_ezpay');
        }

        // Make sure the enrollment dates are valid
        if (!empty($data['enrolenddate']) && !empty($data['enrolstartdate'])) {
            if ($data['enrolstartdate'] >= $data['enrolenddate']) {
                $errors['enrolenddate'] = get_string('enrolenddaterror', 'enrol_ezpay');
            }
        }

        return $errors;
    }
}
