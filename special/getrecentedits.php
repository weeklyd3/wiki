<?php 
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
    ?><li>(<a href="index.php?title=Special:old&revision=<?php echo $edit->revid; ?>&page=<?php echo htmlspecialchars(urlencode($edit->title)); ?>">archive</a> | <a href="index.php?action=history&title=<?php echo htmlspecialchars(urlencode($edit->title)); ?>"></a>) <?php echo formatDate($edit->time); ?> <?php echo userlink(userinfo($edit->author)->username); ?> [[<a href="index.php?title=<?php echo htmlspecialchars(urlencode($edit->title)); ?>"><?php echo htmlspecialchars($edit->title); ?></a>]] (<?php echo htmlspecialchars($edit->summary); ?>)</li><?php
}
?></ul><?php ;