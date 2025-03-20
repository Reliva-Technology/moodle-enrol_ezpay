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
 * API test file for IIUM EzPay payment gateway.
 *
 * @package    paygw_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

// Get the payment gateway configuration
$config = (object) get_config('paygw_ezpay');

// Process form submission
$customdata = false;
$customheaders = false;
$customurl = false;
$testresult = null;
$info = null;
$response = null;
$useragent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
$contenttype = 'application/x-www-form-urlencoded';

if (isset($_POST['submit'])) {
    // Get form data
    $customurl = optional_param('custom_url', '', PARAM_URL);
    $customdata = optional_param('custom_data', '', PARAM_RAW);
    $customheaders = optional_param('custom_headers', '', PARAM_RAW);
    $verifypeer = optional_param('verify_peer', 0, PARAM_BOOL);
    $verifyhost = optional_param('verify_host', 0, PARAM_BOOL);
    $useragent = optional_param('user_agent', $useragent, PARAM_RAW);
    $contenttype = optional_param('content_type', $contenttype, PARAM_RAW);
    $sendmethod = optional_param('send_method', 'form', PARAM_ALPHA);
    
    // Prepare the API test
    try {
        // Use custom URL if provided, otherwise use the configured URL
        $url = !empty($customurl) ? $customurl : $config->apiurl;
        
        // Parse custom data if provided
        $data = [];
        if (!empty($customdata)) {
            // Try to parse as JSON first
            $jsonData = json_decode($customdata, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data = $jsonData;
            } else {
                // Try to parse as key:value pairs
                $lines = explode("\n", $customdata);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    
                    $parts = explode(':', $line, 2);
                    if (count($parts) == 2) {
                        $data[trim($parts[0])] = trim($parts[1]);
                    }
                }
            }
        } else {
            // Use default test data
            $data = [
                'TRANS_ID' => 'TEST-' . time(),
                'AMOUNT' => '10.00',
                'MERCHANT_CODE' => $config->merchantcode,
                'SERVICE_CODE' => !empty($config->servicecode) ? $config->servicecode : '001',
                'RETURN_URL' => (new \moodle_url('/payment/gateway/ezpay/process.php'))->out(false),
                'CANCEL_URL' => (new \moodle_url('/payment/gateway/ezpay/cancel.php'))->out(false),
                'EMAIL' => $USER->email,
                'SOURCE' => 'MOODLE-TEST',
                'PAYEE_ID' => $USER->id,
                'PAYEE_NAME' => fullname($USER),
                'PAYEE_TYPE' => 'OTHRS',
                'PAYMENT_DETAILS' => 'API Test Payment'
            ];
        }
        
        // Parse custom headers if provided
        $headers = [];
        if (!empty($customheaders)) {
            $lines = explode("\n", $customheaders);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                $parts = explode(':', $line, 2);
                if (count($parts) == 2) {
                    $headers[] = trim($parts[0]) . ': ' . trim($parts[1]);
                }
            }
        }
        
        // Add content type header if not already present
        $hasContentType = false;
        foreach ($headers as $header) {
            if (stripos($header, 'Content-Type:') === 0) {
                $hasContentType = true;
                break;
            }
        }
        
        if (!$hasContentType) {
            $headers[] = 'Content-Type: ' . $contenttype;
        }
        
        // Initialize cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        
        // Set the data based on content type
        if ($sendmethod === 'json') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            // If no content-type header was manually set, set it to application/json
            if (!$hasContentType) {
                $headers[count($headers) - 1] = 'Content-Type: application/json';
            }
        } else if ($sendmethod === 'raw') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $customdata);
        } else {
            // Default to form-urlencoded
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifypeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifyhost ? 2 : 0);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        
        // Set custom headers if provided
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        // Create a temporary file to store the verbose output
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
        
        // Execute the request
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        
        // Check for connectivity issues
        if ($response === false) {
            $testresult = 'error';
            $error = curl_error($ch);
        } else {
            $testresult = 'success';
        }
        
        // Get verbose information
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        fclose($verbose);
        
        curl_close($ch);
        
    } catch (Exception $e) {
        $testresult = 'error';
        $error = $e->getMessage();
    }
}

// Set up the page
$PAGE->set_context(\context_system::instance());
$PAGE->set_url(new moodle_url('/payment/gateway/ezpay/api_test.php'));
$PAGE->set_title('API Test');
$PAGE->set_heading('API Test');

echo $OUTPUT->header();

// Display the API test form
echo '<div class="container">';
echo '<h3>EzPay API Connectivity Test</h3>';

// Display configuration (hide sensitive info)
echo '<div class="card mb-4">';
echo '<div class="card-header"><h4>Configuration</h4></div>';
echo '<div class="card-body">';
echo '<pre>';
echo 'API URL: ' . $config->apiurl . "\n";
echo 'Merchant Code: ' . (isset($config->merchantcode) ? substr($config->merchantcode, 0, 3) . '****' : 'Not set') . "\n";
echo 'Service Code: ' . (isset($config->servicecode) ? $config->servicecode : 'Not set') . "\n";
echo '</pre>';
echo '</div>';
echo '</div>';

// Display the test form
echo '<div class="card mb-4">';
echo '<div class="card-header"><h4>API Test Form</h4></div>';
echo '<div class="card-body">';
echo '<form method="post" action="">';

// Custom URL field
echo '<div class="form-group mb-3">';
echo '<label for="custom_url">API URL (leave empty to use configured URL)</label>';
echo '<input type="text" class="form-control" id="custom_url" name="custom_url" value="' . htmlspecialchars($customurl ?: $config->apiurl) . '">';
echo '</div>';

// Custom data field
echo '<div class="form-group mb-3">';
echo '<label for="custom_data">Request Data (JSON or key:value format, leave empty for default test data)</label>';
echo '<textarea class="form-control" id="custom_data" name="custom_data" rows="10">' . htmlspecialchars($customdata ?: '') . '</textarea>';
echo '</div>';

// Send method options
echo '<div class="form-group mb-3">';
echo '<label>Send Method</label>';
echo '<div class="form-check">';
echo '<input class="form-check-input" type="radio" name="send_method" id="send_method_form" value="form" checked>';
echo '<label class="form-check-label" for="send_method_form">Form URL-encoded (standard form submission)</label>';
echo '</div>';
echo '<div class="form-check">';
echo '<input class="form-check-input" type="radio" name="send_method" id="send_method_json" value="json">';
echo '<label class="form-check-label" for="send_method_json">JSON (convert data to JSON)</label>';
echo '</div>';
echo '<div class="form-check">';
echo '<input class="form-check-input" type="radio" name="send_method" id="send_method_raw" value="raw">';
echo '<label class="form-check-label" for="send_method_raw">Raw (send exactly as entered)</label>';
echo '</div>';
echo '</div>';

// Content type field
echo '<div class="form-group mb-3">';
echo '<label for="content_type">Content-Type Header</label>';
echo '<input type="text" class="form-control" id="content_type" name="content_type" value="' . htmlspecialchars($contenttype) . '">';
echo '<small class="form-text text-muted">Common values: application/x-www-form-urlencoded, application/json, multipart/form-data</small>';
echo '</div>';

// User agent field
echo '<div class="form-group mb-3">';
echo '<label for="user_agent">User Agent</label>';
echo '<input type="text" class="form-control" id="user_agent" name="user_agent" value="' . htmlspecialchars($useragent) . '">';
echo '</div>';

// Custom headers field
echo '<div class="form-group mb-3">';
echo '<label for="custom_headers">Custom Headers (key:value format, one per line)</label>';
echo '<textarea class="form-control" id="custom_headers" name="custom_headers" rows="3">' . htmlspecialchars($customheaders ?: '') . '</textarea>';
echo '<small class="form-text text-muted">Example: Accept: application/json</small>';
echo '</div>';

// SSL verification options
echo '<div class="form-check mb-3">';
echo '<input type="checkbox" class="form-check-input" id="verify_peer" name="verify_peer" value="1">';
echo '<label class="form-check-label" for="verify_peer">Verify SSL certificate (CURLOPT_SSL_VERIFYPEER)</label>';
echo '</div>';

echo '<div class="form-check mb-3">';
echo '<input type="checkbox" class="form-check-input" id="verify_host" name="verify_host" value="1">';
echo '<label class="form-check-label" for="verify_host">Verify SSL host (CURLOPT_SSL_VERIFYHOST)</label>';
echo '</div>';

// Submit button
echo '<button type="submit" name="submit" class="btn btn-primary">Test API Connection</button>';
echo '</form>';
echo '</div>';
echo '</div>';

// Display default test data
echo '<div class="card mb-4">';
echo '<div class="card-header"><h4>Default Test Data</h4></div>';
echo '<div class="card-body">';
echo '<pre>';
$defaultData = [
    'TRANS_ID' => 'TEST-' . time(),
    'AMOUNT' => '10.00',
    'MERCHANT_CODE' => $config->merchantcode,
    'SERVICE_CODE' => !empty($config->servicecode) ? $config->servicecode : '001',
    'RETURN_URL' => (new \moodle_url('/payment/gateway/ezpay/process.php'))->out(false),
    'EMAIL' => $USER->email,
    'SOURCE' => 'MOODLE-TEST',
    'PAYEE_ID' => $USER->id,
    'PAYEE_NAME' => fullname($USER),
    'PAYEE_TYPE' => 'OTHRS',
    'PAYMENT_DETAILS' => 'API Test Payment'
];
print_r($defaultData);
echo '</pre>';
echo '<button class="btn btn-secondary" onclick="document.getElementById(\'custom_data\').value = JSON.stringify(' . json_encode($defaultData) . ', null, 2);">Load Default Data as JSON</button> ';
echo '<button class="btn btn-secondary" onclick="loadAsKeyValue();">Load Default Data as Key:Value</button>';
echo '</div>';
echo '</div>';

// Display Postman-like curl command
echo '<div class="card mb-4">';
echo '<div class="card-header"><h4>Equivalent cURL Command</h4></div>';
echo '<div class="card-body">';
echo '<p>You can use this command to test the API from the command line:</p>';
echo '<pre id="curl-command">';
echo 'curl -X POST \\
  "' . $config->apiurl . '" \\
  -H "Content-Type: application/x-www-form-urlencoded" \\
  -d "' . http_build_query($defaultData) . '"';
echo '</pre>';
echo '</div>';
echo '</div>';

// Display test results if available
if ($testresult) {
    echo '<div class="card mb-4">';
    echo '<div class="card-header"><h4>Test Results</h4></div>';
    echo '<div class="card-body">';
    
    if ($testresult === 'error') {
        echo '<div class="alert alert-danger">Connection Error: ' . (isset($error) ? $error : 'Unknown error') . '</div>';
    } else {
        echo '<div class="alert alert-success">Connection Successful</div>';
    }
    
    // Display request details
    echo '<h5>Request Details</h5>';
    echo '<pre>';
    echo 'URL: ' . $url . "\n";
    echo 'Method: POST' . "\n";
    echo 'User Agent: ' . $useragent . "\n";
    
    if ($sendmethod === 'json') {
        echo 'Data (JSON): ' . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    } else if ($sendmethod === 'raw') {
        echo 'Data (Raw): ' . htmlspecialchars($customdata) . "\n";
    } else {
        echo 'Data (Form): ' . http_build_query($data) . "\n";
        echo 'Data (Decoded): ' . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }
    
    if (!empty($headers)) {
        echo 'Headers: ' . json_encode($headers, JSON_PRETTY_PRINT) . "\n";
    }
    echo '</pre>';
    
    // Display response details
    if ($info) {
        echo '<h5>Response Details</h5>';
        echo '<pre>';
        echo 'HTTP Code: ' . $info['http_code'] . "\n";
        echo 'Total Time: ' . $info['total_time'] . " seconds\n";
        echo 'Content Type: ' . $info['content_type'] . "\n";
        echo '</pre>';
    }
    
    // Display verbose log
    if (isset($verboseLog)) {
        echo '<h5>Connection Log</h5>';
        echo '<pre>';
        echo htmlspecialchars($verboseLog);
        echo '</pre>';
    }
    
    // Display the raw response
    if ($response !== false) {
        echo '<h5>Raw Response</h5>';
        echo '<pre>';
        echo htmlspecialchars($response);
        echo '</pre>';
        
        // Try to decode JSON response
        $result = json_decode($response);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            echo '<h5>Decoded Response</h5>';
            echo '<pre>';
            print_r($result);
            echo '</pre>';
            
            // Check if we have a redirect URL
            if (!empty($result->redirect_url)) {
                echo '<div class="alert alert-success">Redirect URL found: ' . $result->redirect_url . '</div>';
                echo '<a href="' . $result->redirect_url . '" class="btn btn-primary" target="_blank">Open Redirect URL</a>';
            } else {
                echo '<div class="alert alert-warning">No redirect URL found in the response.</div>';
            }
        } else {
            // Check if it's HTML response
            if (stripos($response, '<!DOCTYPE html>') !== false || stripos($response, '<html') !== false) {
                echo '<div class="alert alert-success">HTML response detected (likely a payment page)</div>';
                
                // Create a form to view the HTML in a new tab
                echo '<form method="post" action="view_html.php" target="_blank">';
                echo '<input type="hidden" name="html_content" value="' . htmlspecialchars($response) . '">';
                echo '<button type="submit" class="btn btn-primary">View HTML Response</button>';
                echo '</form>';
            } else {
                echo '<div class="alert alert-danger">Failed to decode JSON response: ' . json_last_error_msg() . '</div>';
            }
        }
    }
    
    echo '</div>';
    echo '</div>';
}

echo '</div>'; // Close container

echo $OUTPUT->footer();

// Add JavaScript to handle the form
echo '<script>
document.addEventListener("DOMContentLoaded", function() {
    // Function to format JSON in the textarea
    document.getElementById("custom_data").addEventListener("blur", function() {
        try {
            const value = this.value.trim();
            if (value && value.startsWith("{") && value.endsWith("}")) {
                const jsonObj = JSON.parse(value);
                this.value = JSON.stringify(jsonObj, null, 2);
            }
        } catch (e) {
            // Not valid JSON, leave as is
        }
    });
});

// Function to load data as key:value pairs
function loadAsKeyValue() {
    const data = ' . json_encode($defaultData) . ';
    let keyValueString = "";
    
    for (const key in data) {
        keyValueString += key + ": " + data[key] + "\n";
    }
    
    document.getElementById("custom_data").value = keyValueString;
}
</script>';

// Create a view_html.php file if it doesn't exist
$viewhtmlpath = __DIR__ . '/view_html.php';
if (!file_exists($viewhtmlpath)) {
    $viewhtmlcontent = '<?php
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
 * View HTML response from API test.
 *
 * @package    paygw_ezpay
 * @copyright  2025 Fadli Saad <fadlisaad@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../../config.php");

// Get the HTML content from POST
$html_content = optional_param("html_content", "", PARAM_RAW);

// Set up the page
$PAGE->set_context(\context_system::instance());
$PAGE->set_url(new moodle_url("/payment/gateway/ezpay/view_html.php"));
$PAGE->set_title("HTML Response Viewer");
$PAGE->set_heading("HTML Response Viewer");

echo $OUTPUT->header();

echo "<h3>HTML Response from API</h3>";

if (!empty($html_content)) {
    // Display in an iframe
    echo "<div class=\"card\">";
    echo "<div class=\"card-header\">HTML Content</div>";
    echo "<div class=\"card-body\">";
    echo "<iframe id=\"html-viewer\" style=\"width:100%; height:600px; border:1px solid #ddd;\"></iframe>";
    echo "</div>";
    echo "</div>";
    
    // Use JavaScript to set the iframe content
    echo "<script>";
    echo "document.addEventListener(\"DOMContentLoaded\", function() {";
    echo "    const iframe = document.getElementById(\"html-viewer\");";
    echo "    const iframeDoc = iframe.contentWindow.document;";
    echo "    iframeDoc.open();";
    echo "    iframeDoc.write(`" . str_replace("`", "\\`", $html_content) . "`);";
    echo "    iframeDoc.close();";
    echo "});";
    echo "</script>";
    
    // Also show the raw HTML
    echo "<div class=\"card mt-4\">";
    echo "<div class=\"card-header\">Raw HTML</div>";
    echo "<div class=\"card-body\">";
    echo "<pre>" . htmlspecialchars($html_content) . "</pre>";
    echo "</div>";
    echo "</div>";
} else {
    echo "<div class=\"alert alert-warning\">No HTML content provided.</div>";
}

echo $OUTPUT->footer();
';
    file_put_contents($viewhtmlpath, $viewhtmlcontent);
}
