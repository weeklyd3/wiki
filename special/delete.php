<?php 
$groups = getUserGroups($_SESSION['userid'], true);
require_once __DIR__ . "/../settings.php";
global $adminUserGroup;
if (!in_array($adminUserGroup, $groups)) {
    $title = 'Permission denied';
    ?><?php echo sysmsg("no-admin-delete-page"); ?><?php
    return;
}
if (!isset($_GET['page'])) {
    $title = 'Nothing to delete';
    ?><?php echo sysmsg('nothing-to-delete'); ?><?php
    return;
}
$page2ID = json_decode(file_get_contents(__DIR__ . "/../pages/page2ID.json"));
$title = $_GET['page'];
if (isset($_POST['delete'])) {
    if (deletePage($title)) {
        $oldtitle = $_GET['page'];
        $title = 'Success';
        new logEntry($_SESSION['userid'], null, $_GET['page'], "delete", $_SESSION['username'] . " deleted page $oldtitle", $_POST['reason']);
        ?><?php echo sysmsg('deleted'); ?><?php 
        return;
    }
    ?><div class="error"><?php echo sysmsg('delete-error'); ?></div><?php
}
if (!isset($page2ID->$title)) {
    $title = 'You cannot delete that';
    ?><?php echo sysmsg('cant-delete-that'); ?><?php
    return;
}
$title = "Delete $title";
global $originalPageName;
?>
<?php echo sysmsg('delete-confirm', htmlspecialchars($_GET['page'])); ?>
<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
<label><?php echo sysmsg('delete-reason'); ?> <input type="text" name="reason" value="" required="required" /></label><div></div>
<input type="submit" name="delete" value="<?php echo htmlspecialchars(strip_tags(sysmsg('delete-submit'))); ?>" />
</form>