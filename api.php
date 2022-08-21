<?php
/*
This file is part of weeklyd3's wiki software.

weeklyd3's wiki software is free software: you can redistribute 
it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either 
version 3 of the License, or (at your option) any later version.

weeklyd3's wiki software is distributed in the hope that it will 
be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public 
License along with weeklyd3's wiki software. If not, see 
<https://www.gnu.org/licenses/>. 
*/
 
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', 'true');
header('Access-Control-Allow-Origin: ' . $_GET['origin'] ?? $_SERVER['HTTP_ORIGIN']);
header('Access-Control-Allow-Credentials: true');
session_start(); 
class apiResponse {
    public $errors = null;
    public $query = null;
    public $warnings = null;
    public function __construct() {
        $this->time = time();
    }
}
class apiError {
    public function __construct(string $type, string $cmt, bool $isYourFault) {
        $this->type = $type;
        $this->reason = $cmt;
        $this->isYourFault = $isYourFault;
    }
}
class apiWarning extends apiError {}
class apiResponseError extends apiResponse {
    public function __construct() {
        $this->errors = array();
    }
    public function addError(string $type, string $cmt, bool $isYourFault = true) {
        array_push($this->errors, new apiError($type, $cmt, $isYourFault));
    }
}
class apiResponseSuccess extends apiResponse {
    public function __construct($response) {
        $this->query = $response;
    }
}
$output = null;
require_once 'pageRender.php';
$action = cleanFilename($_GET['action']) ?? 'none';
$filteredQuery = $_GET;
unset($filteredQuery['action']);
function add_error(string $type, string $cmt, bool $yourFault = true) {
    global $output;
    if (!isset($output->errors)) $output->errors = array();
    array_push($output->errors, new apiError($type, $cmt, $yourFault));
}
function add_warning(string $type, string $cmt, bool $yourFault = true) {
    global $output;
    if (!isset($output->warnings)) $output->warnings = array();
    array_push($output->warnings, new apiWarning($type, $cmt, $yourFault));
}
function set_response($response) {
    global $output;
    $output->query = $response;
}
if (file_exists("api/$action.php")) {
    require_once "api/$action.php";
    $output = new apiResponse;
    api($action, $filteredQuery);
}
if (!isset($output)) {
    $output = new apiResponseError;
    $output->addError("internal_server_error", "The server did not provide any API output", false);
}
$output = json_encode($output, 128);
if (isset($_GET['json'])) {
    header('Content-Type: application/json');
    echo $output;
    exit;
}  
?>
<!DOCTYPE html>
<html><head><meta name="viewport" content="width=device-width,initial-scale=1.0" /><title>API Result</title></head><body>
<h1>Result of API call</h1>
<p>Below is the result of your API call. If you were expecting neat JSON output, add a <code>json</code> parameter to your URL instead of attempting to scrape this page.</p>
<pre><code><?php echo htmlspecialchars($output); ?></code></pre>
</body>
</html>
