<?php

class enrol_ezpay_plugin extends enrol_plugin {
    public function get_instance_name($instance) {
        return get_string('pluginname', 'enrol_ezpay');
    }

    public function enrol_user($instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = null) {
        global $DB;

        // Assume payment verification is successful.
        $context = context_course::instance($instance->courseid);

        // Enrol the user in the course.
        $this->enrol_user($instance, $userid, $roleid, $timestart, $timeend, $status);

        // Assign role if specified.
        if ($roleid) {
            role_assign($roleid, $userid, $context->id);
        }
    }
}
