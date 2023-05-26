<?php
// originally in special/
$title = "Rebuild page index";
echo sysmsg("page-index-rebuild-intro");
if (!isset($_POST['rebuild'])) {
    ?><form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post"><input type="submit" name="rebuild" value="<?php echo sysmsgPlain('page-index-rebuild-submit'); ?>" /></form><?php
    return;
}
//new logEntry($_SESSION['userid'], null, null, 'page-index-operation', 'Page index rebuild started', 'No reason');
$log = array(sysmsgPlain("page-index-rebuild-start"));
array_push($log, sysmsgPlain("page-index-rebuild-stage1"));
$stage1Removed = 0;
$page2ID = json_decode(file_get_contents("pages/page2ID.json"));
$num = 0;
foreach ((array) $page2ID as $key => $value) {
    $num++;
    array_push($log, sysmsgPlain("page-index-rebuild-stage1-checking", $key, $value, $num, count((array) $page2ID)));
    if (is_dir(__DIR__ . "/../pages/data/$key")) {
        array_push($log, sysmsgPlain("page-index-rebuild-stage1-pass"));
        continue;
    }
    array_push($log, sysmsgPlain("page-index-rebuild-stage1-fail"));
    unset($page2ID[$key]);
}
array_push($log, sysmsgPlain('page-index-rebuild-stage1-done', $num));
fwrite(fopen("pages/page2ID.json", "w+"), json_encode($page2ID));
?>
<pre><code><?php 
foreach ($log as $entry) {
    echo "$entry\n";
}
?></code></pre>