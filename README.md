[![ci](https://github.com/Reliva-Technology/ezpay-moodle-plugin/actions/workflows/ci.yml/badge.svg)](https://github.com/Reliva-Technology/ezpay-moodle-plugin/actions/workflows/ci.yml)
# Moodle Payment Gateway EZPay

Welcome to the EZPay plugin repository for Moodle. This plugin enables payment-based enrolment in Moodle courses using EZPay payment gateway. Following Moodle's documentation on enrolment plugins, EZPay provides a seamless way to handle course payments.

## Key Features
- **EZPay Payment Gateway Integration:** Seamless payment-based enrolment for Moodle courses.
- **Payment Status Requery:** Users can manually requery the status of their payment and view a detailed status page.
- **Automatic Status Updates:** A scheduled task checks all pending/failed transactions every 15 minutes and updates enrolments automatically.
- **User-Friendly Templates:** All payment and status pages use Mustache templates for a modern, consistent UI.
- **Full Localization:** All user-facing messages are fully localized for easy translation and multi-language support.

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

### Payment Status Requery (Manual & Automatic)
- **Manual:** Users can check the status of their payment at any time using the "Requery Payment Status" feature. The result is displayed using a modern, localized template.
- **Automatic:** The plugin includes a scheduled task that runs every 15 minutes to automatically requery the status of all pending or failed transactions. Successful payments will trigger immediate enrolment.

### Templates & Localization
- All payment and status pages are rendered using Mustache templates for a modern, user-friendly experience.
- All user-facing messages are localized for easy translation. To add new languages, update the `lang/en/enrol_ezpay.php` file accordingly.

### Cron Setup
- The scheduled task (`Requery pending EZPay payments`) is registered automatically. You can view and manage it under **Site administration → Server → Scheduled tasks**.

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
