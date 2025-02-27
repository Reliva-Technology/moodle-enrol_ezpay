<?php

$string['pluginname'] = 'EzPay Enrolment';
$string['pluginname_desc'] = 'Allows enrolment using the EzPay payment gateway.';

// Enrollment settings
$string['defaultenrol'] = 'Add to new courses';
$string['defaultenrol_desc'] = 'Enable this method by default for all new courses.';
$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during EzPay enrollment.';
$string['enrolperiod'] = 'Enrollment duration';
$string['enrolperiod_desc'] = 'Default length of time that the enrollment is valid. If set to zero, the enrollment duration will be unlimited by default.';
$string['cost'] = 'Default enrollment fee';
$string['cost_desc'] = 'The default amount to be charged for course enrollment.';
$string['costerror'] = 'The enrollment fee must be a number.';
$string['currency'] = 'Currency';
$string['currency_desc'] = 'Default currency for enrollment fees.';
$string['assignrole'] = 'Assign role';
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
