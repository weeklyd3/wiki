<?php
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', 'true');
header('Access-Control-Allow-Origin', $_GET['origin'] ?? $_SERVER['HTTP_ORIGIN']);
header('Access-Control-Allow-Credentials', 'true');
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
function cleanFilename($stuff) {
	$illegal = array(" ","?","/","\\","*","|","<",">",'"');
	// legal characters
	$legal = array("-","_","_","_","_","_","_","_","_");
	$cleaned = str_replace($illegal,$legal,$stuff);
	return $cleaned;
}
$output = null;
$action = $_GET['action'] ?? 'none';
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
    require "api/$action.php";
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