<?php

$string['pluginname'] = 'IIUM EzPay Enrolment';
$string['pluginname_desc'] = 'Allows enrolment using the IIUM EzPay payment gateway.';

// Enrollment settings
$string['defaultenrol'] = 'Add to new courses';
$string['defaultenrol_desc'] = 'Enable this method by default for all new courses.';
$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during IIUM EzPay enrollment.';
$string['enrolperiod'] = 'Enrollment duration';
$string['enrolperiod_desc'] = 'Default length of time that the enrollment is valid. If set to zero, the enrollment duration will be unlimited by default.';
$string['enrolperiod_help'] = 'Length of time that the enrollment is valid, starting from the moment the user is enrolled. If disabled, the enrollment duration will be unlimited.';
$string['cost'] = 'Default enrollment fee';
$string['cost_desc'] = 'The default amount to be charged for course enrollment.';
$string['cost_help'] = 'If you set an amount here, users will need to pay this amount to enroll in the course. If the amount is zero, then users do not have to pay to enroll.';
$string['costerror'] = 'The enrollment fee must be a number.';
$string['currency'] = 'Currency';
$string['currency_desc'] = 'Default currency for enrollment fees.';
$string['currency_help'] = 'The currency code for the payment, such as MYR for Malaysian Ringgit.';
$string['assignrole'] = 'Assign role';
$string['assignrole_help'] = 'Select the role that should be assigned to users when they enroll in this course using EzPay.';
$string['enrolenddate'] = 'End date';
$string['enrolenddate_help'] = 'If enabled, users can be enrolled until this date only.';
$string['enrolenddaterror'] = 'The enrollment end date cannot be earlier than the start date.';
$string['enrolstartdate'] = 'Start date';
$string['enrolstartdate_help'] = 'If enabled, users can be enrolled from this date onward only.';
$string['expiredaction'] = 'Enrollment expiration action';
$string['expiredaction_help'] = 'Select action to carry out when user enrollment expires. Please note that some user data and settings are purged from course during course unenrollment.';
$string['paymentaccount'] = 'Payment account';
$string['paymentaccount_help'] = 'Enrollment fees will be paid to this account.';
$string['status'] = 'Allow EzPay enrollments';
$string['status_desc'] = 'Allow users to use EzPay to enroll into a course by default.';
$string['status_help'] = 'This setting determines whether users can use EzPay to enroll in this course.';
$string['unenrolselfconfirm'] = 'Do you really want to unenroll yourself from course "{$a}"?';
$string['canntenrol'] = 'Enrollment is disabled or inactive';
$string['canntenrolearly'] = 'You cannot enroll yet; enrollment starts on {$a}.';
$string['canntenrollate'] = 'You cannot enroll anymore, since enrollment ended on {$a}.';
$string['cohortnonmemberinfo'] = 'Only members of cohort \'{$a}\' can enroll.';
$string['cohortonly'] = 'Only cohort members';
$string['cohortonly_help'] = 'Enrollment may be restricted to members of a specified cohort only. Note that changing this setting has no effect on existing enrollments.';
$string['customwelcomemessage'] = 'Custom welcome message';
$string['customwelcomemessage_help'] = 'A custom welcome message may be added as plain text or Moodle-auto format, including HTML tags and multi-lang tags.';
$string['maxenrolled'] = 'Max enrolled users';
$string['maxenrolled_help'] = 'Specifies the maximum number of users that can enroll using EzPay. 0 means no limit.';
$string['maxenrolledreached'] = 'Maximum number of users allowed to enroll using EzPay was already reached.';
$string['sendcoursewelcomemessage'] = 'Send course welcome message';
$string['sendcoursewelcomemessage_help'] = 'When a user enrolls in the course, they may be sent a welcome message email. If sent from the course contact (by default the teacher), and more than one user has this role, the email is sent from the first user assigned the role.';
