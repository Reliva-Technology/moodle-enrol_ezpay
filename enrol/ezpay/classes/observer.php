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
 * Event observer for enrol_ezpay.
 *
 * @package    enrol_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for enrol_ezpay.
 *
 * @package    enrol_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_ezpay_observer {
    /**
     * Triggered via course_created event.
     *
     * @param \core\event\course_created $event
     * @return bool true on success
     */
    public static function course_created(\core\event\course_created $event) {
        global $DB;
        
        if (enrol_is_enabled('ezpay')) {
            $ezpay = enrol_get_plugin('ezpay');
            
            // Check if the plugin should be added to new courses
            if ($ezpay->get_config('defaultenrol')) {
                $course = $DB->get_record('course', array('id' => $event->objectid));
                
                if ($course) {
                    // Add ezpay enrollment instance to the new course
                    $ezpay->add_default_instance($course);
                }
            }
        }
        
        return true;
    }
}
