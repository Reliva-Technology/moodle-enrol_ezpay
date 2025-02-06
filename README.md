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
   - Enter your IIUM EzPay Merchant Code
   - Enter the IIUM EzPay API URL (default: https://ezpay.iium.edu.my/payment/request)

## API Parameters

The plugin uses the following API parameters when communicating with IIUM EzPay:

- `TRANS_ID`: Unique transaction ID (Moodle payment ID)
- `MERCHANT_CODE`: Your IIUM EzPay merchant code
- `RETURN_URL`: URL where user will be redirected after payment
- `AMOUNT`: Payment amount
- `EMAIL`: User's email address
- `SOURCE`: Set to "MOODLE"

## Support

For support, please contact [fadlisaad@gmail.com](mailto:fadlisaad@gmail.com).
