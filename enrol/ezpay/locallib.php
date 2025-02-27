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
 * EzPay enrolment plugin local library.
 *
 * @package    enrol_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * EzPay payment form class
 */
class enrol_ezpay_payment_form {
    /** @var stdClass Course enrollment instance */
    protected $instance;
    
    /** @var int User ID */
    protected $userid;
    
    /**
     * Constructor
     * 
     * @param stdClass $instance Course enrollment instance
     * @param int $userid User ID
     */
    public function __construct($instance, $userid) {
        $this->instance = $instance;
        $this->userid = $userid;
    }
    
    /**
     * Render the payment form/button
     * 
     * @return string HTML output
     */
    public function render() {
        global $CFG, $DB, $USER, $OUTPUT;
        
        $course = $DB->get_record('course', array('id' => $this->instance->courseid), '*', MUST_EXIST);
        
        $context = context_course::instance($course->id);
        $fullname = format_string($course->fullname, true, array('context' => $context));
        
        // Create payment description
        $description = get_string('pluginname', 'enrol_ezpay') . ': ' . $fullname;
        
        // Format cost
        $cost = format_float($this->instance->cost, 2, true);
        
        // Create a payment component
        $component = 'enrol_ezpay';
        $paymentarea = 'fee';
        $itemid = $this->instance->id;
        
        // Create payment button
        $button = new \core_payment\form\account_gateway_button(
            $component,
            $paymentarea,
            $itemid,
            $this->instance->cost,
            $this->instance->currency,
            $this->userid,
            $description
        );
        
        return $button->render();
    }
}
