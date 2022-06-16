<?php 
$groups = getUserGroups($_SESSION['userid'], true);
require_once __DIR__ . "/../settings.php";
global $adminUserGroup;
if (!in_array($adminUserGroup, $groups)) {
    $title = 'Permission denied';
    ?><p>You need administrator access to delete pages. Sorry.</p><?php
    return;
}
if (!isset($_GET['page'])) {
    $title = 'Nothing to delete';
    ?><p>You need to specify something to throw in the trash.</p><?php
    return;
}
$page2ID = json_decode(file_get_contents(__DIR__ . "/../pages/page2ID.json"));
$title = $_GET['page'];
if (isset($_POST['delete'])) {
    if (deletePage($title)) {
        $oldtitle = $_GET['page'];
        $title = 'Success';
        new logEntry($_SESSION['userid'], null, $_GET['page'], "delete", $_SESSION['username'] . " deleted page $oldtitle", $_POST['reason']);
        ?><p>The page has been deleted.</p><?php 
        return;
    }
    ?><div class="error">There seems to be an error. Please make sure you have the sufficient permissions.</div><?php
}
if (!isset($page2ID->$title)) {
    $title = 'You cannot delete that';
    ?><p>The page does not exist. Perhaps it was already deleted?</p><?php
    return;
}
$title = "Delete $title";
global $originalPageName;
?>
<p>Are you sure you want to delete the page <strong><?php echo htmlspecialchars($_GET['page']); ?></strong>?</p>
<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
<label>Reason for deletion: <input type="text" name="reason" value="" required="required" /></label><div></div>
<input type="submit" name="delete" value="Yes, I am sure" />
</form>