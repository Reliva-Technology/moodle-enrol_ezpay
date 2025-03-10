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
     * Get the EzPay API endpoint based on environment setting
     * 
     * @return string API endpoint URL
     */
    protected function get_api_endpoint() {
        global $CFG;
        
        // Get environment setting
        $environment = get_config('enrol_ezpay', 'environment');
        
        // Return appropriate endpoint based on environment
        if ($environment === 'production') {
            return 'https://ezpay.iium.edu.my/payment/request';
        } else {
            // Default to staging
            return 'https://ezypay-stg.iium.edu.my/payment/request';
        }
    }
    
    /**
     * Render the payment form/button
     * 
     * @return string HTML output
     */
    public function render() {
        global $CFG, $DB, $USER, $OUTPUT, $PAGE;
        
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
        
        // Check if redirect method should be used
        $config = get_config('paygw_ezpay');
        $useredirect = !empty($config->useredirect);
        
        if ($useredirect) {
            // Create a direct payment link instead of modal button
            $redirecturl = new moodle_url('/payment/gateway/ezpay/redirect.php', [
                'component' => $component,
                'paymentarea' => $paymentarea,
                'itemid' => $itemid,
                'description' => $description
            ]);
            
            $button = new single_button($redirecturl, get_string('sendpaymentbutton', 'enrol_fee'), 'get');
            return $OUTPUT->render($button);
        } else {
            // Create a direct payment link for the button
            $redirecturl = new moodle_url('/payment/gateway/ezpay/redirect.php', [
                'component' => $component,
                'paymentarea' => $paymentarea,
                'itemid' => $itemid,
                'description' => $description
            ]);
            
            // Create payment button with core payment API
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
}
