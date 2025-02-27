<?php

class enrol_ezpay_plugin extends enrol_plugin {
    /**
     * Returns the name of this enrollment plugin
     * @param stdClass $instance
     * @return string
     */
    public function get_instance_name($instance) {
        return get_string('pluginname', 'enrol_ezpay');
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
    public function enrol_user($instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = null) {
        global $DB;

        // Get the context
        $context = context_course::instance($instance->courseid);

        // Call parent method to handle the actual enrollment
        parent::enrol_user($instance, $userid, $roleid, $timestart, $timeend, $status);

        // Assign role if specified
        if ($roleid) {
            role_assign($roleid, $userid, $context->id);
        }
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
}
