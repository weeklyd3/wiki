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

if (!isset($_GET['page'])) {
    echo sysmsg('no-page-to-rollback');
    return;
}
$page = $_GET['page'];
if (!page_exists($page)) {
    echo sysmsg('rollback-page-doesnt-exist');
    return;
}
$page2ID = json_decode(file_get_contents(__DIR__ . "/../pages/page2ID.json"));
$pageid = $page2ID->$page;
if (!isset($_GET['revision'])) {
    $title = "Rollback failed";
    echo sysmsg('rollback-revision-failed');
    return;
}
$rev = $_GET['revision'];
$revisions = json_decode(file_get_contents(__DIR__ . "/../pages/data/$pageid/revisions.json"));
if (!isset($revisions[$rev])) {
    $title = "Rollback failed";
    echo sysmsg('rollback-revision-does-not-exist');
    return;
}
$oldrev = $rev;
if (!isset($revisions[$rev + 1])) {
    $title = "Rollback failed";
    echo sysmsg('rollback-cant-revert-to-current-revision');
    return;
}
if (!canEditPage($page)[0]) {
    $title = "Rollback failed";
    $why = canEditPage($page)[1];
    echo sysmsg("cant-edit-$why");
    return;
}
$rev = count($revisions) - 1 - $oldrev;
if (isset($_POST['do-it'])) {
    $author = userinfo($revisions[$oldrev]->author)->username;
    $summary = "Restored revision $oldrev by [[User:$author|$author]] ([[User talk:$author|talk]])";
    if ($_POST['summary']) $summary .= ": {$_POST['summary']}";
    $before = file_get_contents(__DIR__ . "/../pages/data/$pageid/pastRevisions/$oldrev/page.md");
    modifyPage($page, $before, $summary);
    $title = "Rollback done";
    echo sysmsg('rollback-done', $summary, $page);
    return;
}
$title = "Confirm rollback";
echo sysmsg('rollback-header');
$curr = page_get_contents($page);
$last = file_get_contents(__DIR__ . "/../pages/data/$pageid/pastRevisions/$oldrev/page.md");
require_once __DIR__ . "/../diff.php";
diff($curr, $last, "Latest revision", "Revision to revert to");
?>
<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
<label><?php echo sysmsgPlain("rollback-summary"); ?>
<input name="summary" />
</label>
<input type="submit" name="do-it" value="<?php echo sysmsgPlain("rollback-submit"); ?>" />
</form>