<?php 
global $originalPageName;
$pagename = $_GET['pagename'] ?? die(sysmsg('no-page-name-to-protect'));
$title = "Protect $pagename";
if (substr($originalPageName, 0, strlen('Interface:')) !== 'Interface:') echo sysmsg('protect-instructions', htmlspecialchars($originalPageName));
else {
    echo sysmsg('cant-protect-interface-page');
    return;
}
$protectionList = json_decode(file_get_contents(__DIR__ . "/../protect.json"));
$protected = in_array($pagename, $protectionList);
echo sysmsg('protect-status-' . ($protected ? 'protected' : 'unprotected'));
if (isset($_POST['reason'])) {
    new logEntry($_SESSION['userid'], null, $pagename, "protect", $_SESSION['userid'] . " protected page $pagename", $_POST['reason']);
    if ($protected) $protectionList = array_diff($protectionList, array($pagename));
    else array_push($protectionList, $pagename);
    $title = 'Success';
    echo sysmsg('page-protection-changed', htmlspecialchars($pagename));
    fwrite(fopen(__DIR__ . "/../protect.json", "w+"), json_encode($protectionList));
    return;
}
?>
<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
<label><?php echo sysmsg('protect-reason'); ?> <input name="reason" required="required" /></label>
<input type="submit" value="<?php echo strip_tags(sysmsg('protect-submit-button')); ?>" />
</form>