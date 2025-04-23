[![ci](https://github.com/Reliva-Technology/ezpay-moodle-plugin/actions/workflows/ci.yml/badge.svg)](https://github.com/Reliva-Technology/ezpay-moodle-plugin/actions/workflows/ci.yml)
# Moodle Payment Gateway EZPay

Welcome to the EZPay plugin repository for Moodle. This plugin enables payment-based enrolment in Moodle courses using EZPay payment gateway. Following Moodle's documentation on enrolment plugins, EZPay provides a seamless way to handle course payments.

## Steps for Integration
1. Download and install the plugin
2. Configure your EZPay merchant credentials
3. Configure the Moodle enrolment with EZPay payment
4. Add 'EZPay Payment' to the Moodle courses where you want to enable paid enrolment

### Installation
After downloading the plugin:
1. Login as admin to your Moodle site
2. Go to **Site administration** -> **Plugins** -> **Install plugins**
3. Choose or drag-and-drop the plugin zip file to the box
4. Click **install plugin from ZIP file**
5. Click **continue** after installation completes

### Configure EZPay Payment as Enrolment Method
1. Go to **Site administration** -> **Plugins** -> **Enrolments** -> **Manage enrol plugins**
2. Locate **EZPay Payment** in the list and ensure it is enabled
3. Set the appropriate **environment** (production/sandbox)
4. Configure enrolment settings within **EZPay Payment**

>***Note: Using incorrect environment settings will result in denied payment access***

### Add EZPay Payment to Courses
1. Navigate to the desired course
2. Go to **Participants**
3. Click the actions menu and select **Enrolment methods**
4. Choose **EZPay Payment** from the Add dropdown menu
5. Set the course cost in **Enrol cost** and click **Add method**

## Support
For support or inquiries, please contact:
- Email: iium@reliva.com.my
- Website: https://reliva.com.my
