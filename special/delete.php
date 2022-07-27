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
 echo sysmsg('delete-confirm', htmlspecialchars($_GET['page'])); ?>
<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
<label><?php echo sysmsg('delete-reason'); ?> <input type="text" name="reason" value="" required="required" /></label><div></div>
<input type="submit" name="delete" value="<?php echo htmlspecialchars(strip_tags(sysmsg('delete-submit'))); ?>" />
</form>