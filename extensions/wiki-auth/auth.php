<?php
add_system_messages(__DIR__ . "/messages.json");
if (!isset($_SESSION['username'])) {
    $title = sysmsg_raw("wiki-auth-login-title");
    echo sysmsg('wiki-auth-log-in');
    return;
}
$hash = hash('sha224', uniqid("", true));
require __DIR__ . "/ids.php";
$fileContents = "<?php\n// This file is automatically modified. Please be careful!\nglobal \$authenticationData;\n\$authenticationData = ";
global $authenticationData;
if (isset($_GET['getdata'])) {
    $id = $_GET['getdata'];
    header('Content-Type: application/json');
    if (!isset($authenticationData[$id])) echo "null";
    else {
        echo json_encode($authenticationData[$id]);
        unset($authenticationData[$id]);
        ob_start();
        var_export($authenticationData);
        $exported = ob_get_clean();
        $fileContents .= $exported;
        $fileContents .= ';';
        fwrite(fopen(__DIR__ . "/ids.php", "w+"), $fileContents);
    }
    exit;
}
$authenticationData[$hash] = array("time" => time(), "name" => $_SESSION['username']);
ob_start();
var_export($authenticationData);
$exported = ob_get_clean();
$fileContents .= $exported;
$fileContents .= ';';
fwrite(fopen(__DIR__ . "/ids.php", "w+"), $fileContents);
if (!isset($_GET['appname'], $_GET['callback'])) {
    echo sysmsg('wiki-auth-nothing-to-go', $hash);
    return;
}
$callbackurl = $_GET['callback'];
if (parse_url($callbackurl, PHP_URL_QUERY) !== null) $callbackurl .= "&authentication-token=" . $hash;
else $callbackurl .= "?authentication-token" . $hash;
if (isset($_GET['failure-callback'])) echo sysmsg('wiki-auth-confirm-with-fail', $_GET['appname'], $callbackurl, $_GET['failure-callback']);
else {  
    echo sysmsg('wiki-auth-confirm', $_GET['appname'], $callbackurl);
}