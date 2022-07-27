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
 
require_once __DIR__ . "/../accounts.php";
$limit = 500; // Feel free to change
$recentEdits = json_decode(file_get_contents(__DIR__ . "/../mod/recentChanges.json"));
// Prevent it from getting too large
$recentEdits = array_slice($recentEdits, 0, $limit + 100);
fwrite(fopen(__DIR__ . '/../mod/recentChanges.json', 'w+'), json_encode($recentEdits));
?>
(Limit: <?php echo $limit; ?> edits)
<ul>
<?php
foreach ($recentEdits as $edit) {
    ?><li>(<a href="index.php?title=Special:old&revision=<?php echo $edit->revid; ?>&page=<?php echo htmlspecialchars(urlencode($edit->title)); ?>">archive</a> | <a href="index.php?action=history&title=<?php echo htmlspecialchars(urlencode($edit->title)); ?>">history</a>) <?php echo formatDate($edit->time); ?> <?php echo userlink(userinfo($edit->author)->username); ?> [[<a href="index.php?title=<?php echo htmlspecialchars(urlencode($edit->title)); ?>"><?php echo htmlspecialchars($edit->title); ?></a>]] (<?php echo htmlspecialchars($edit->summary); ?>)</li><?php
}
?></ul><?php ;