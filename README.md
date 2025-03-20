# IIUM EzPay Payment Gateway for Moodle

This plugin integrates IIUM EzPay payment gateway with Moodle's payment subsystem, allowing users to make payments through the IIUM EzPay platform.

## Features

- Seamless integration with Moodle's payment system
- Supports payment validation and verification
- Configurable merchant settings
- Automatic payment status updates
- Secure payment processing

## Requirements

- Moodle 4.1 or higher
- PHP 7.4 or higher
- curl extension enabled

## Installation

1. Create a directory `payment/gateway/ezpay` in your Moodle installation
2. Copy all the plugin files into this directory
3. Visit your Moodle site's administration area to complete the installation
4. Configure the plugin settings

## Configuration

1. Go to Site administration > Plugins > Payment gateways > Manage payment gateways
2. Enable the IIUM EzPay payment gateway
3. Configure the following settings:
   - Enter your IIUM EzPay Merchant Code (default to 'moodle')
   - Enter the IIUM EzPay API URL (default: https://ezpay.iium.edu.my/payment/request)

## API Parameters

The plugin uses the following API parameters when communicating with IIUM EzPay:

- `TRANS_ID`: Unique transaction ID (Moodle payment ID)
- `MERCHANT_CODE`: Your IIUM EzPay merchant code (default to 'moodle')
- `RETURN_URL`: URL where user will be redirected after payment
- `AMOUNT`: Payment amount
- `EMAIL`: User's email address
- `SOURCE`: Set to "MOODLE"

## API Testing Tool

The plugin includes a comprehensive API testing tool to help diagnose connectivity issues and test the payment gateway integration:

1. Access the API testing tool at: `https://your-moodle-site.com/payment/gateway/ezpay/api_test.php`
2. The tool provides the following features:
   - Test API connectivity with different request configurations
   - Customize headers, user agent, and content type
   - View detailed request and response information
   - Test different data formats (JSON, form-encoded, raw)
   - View HTML responses in a separate viewer
   - Generate equivalent cURL commands for command-line testing

### Troubleshooting with the API Test Tool

If you're experiencing issues with the payment gateway:

1. Compare the API responses between Postman and the Moodle server
2. Try different user agent strings and content types
3. Check for HTML responses that might not be properly detected
4. Use the verbose connection logs to identify any network or SSL issues

## Support

For support, please contact [fadlisaad@gmail.com](mailto:fadlisaad@gmail.com).
