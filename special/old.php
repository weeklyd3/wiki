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
 
$pagetitle = $_GET['page'];
$revid     = (int) $_GET['revision'];
$title = "Version $revid of $pagetitle";
$ids = json_decode(file_get_contents(__DIR__ . '/../pages/page2ID.json'));
if (!isset($ids->$pagetitle)) {
    ?><div class="error">Could not find the page "<?php echo htmlspecialchars($pagetitle); ?>"</div><?php
    return;
}
$id = $ids->$pagetitle;
$revisions = json_decode(file_get_contents(__DIR__ . "/../pages/data/$id/revisions.json"));
$revisions = array_reverse($revisions);
if (!isset($revisions[$revid])) {
    ?><div class="error">Could not find revision <?php echo $revid; ?> of page <?php echo htmlspecialchars($pagetitle); ?></div><?php
    return;
}
?>
<small><a href="index.php?title=<?php echo htmlspecialchars(urlencode($pagetitle)); ?>">Back to page</a></small>
<p>Note: This is an old version of the page. For the newest version, <a href="index.php?title=<?php echo htmlspecialchars(urlencode($pagetitle)); ?>">go to the latest revision</a>.</p>
<h2>Diff with previous version</h2>
<?php
require 'diff.php';
$oldrevid = $revid - 1;
if ($revid - 1 >= 0) $old = file_get_contents(__DIR__ . "/../pages/data/$id/pastRevisions/$oldrevid/page.md");
else $old = '';
diff($old, file_get_contents(__DIR__ . "/../pages/data/$id/pastRevisions/$revid/page.md"));
?>
<h2>Rendered Markdown</h2>
<?php 
require_once __DIR__ . '/../markdown/parsedown/parsedown.php';
$Parsedown = new Parsedown;
echo $Parsedown->text(file_get_contents(__DIR__ . "/../pages/data/$id/pastRevisions/$revid/page.md")); ?>