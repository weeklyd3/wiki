<?php 
$title = 'Search results';
$query = $_GET['query'] ?? '';
require_once __DIR__ . '/../accounts.php';
require_once __DIR__ . '/../date.php';
?>
<form action="index.php" method="GET">
            <label><span class="hidden2eyes">Search query: </span>
                <input type="search" name="query" style="box-sizing: border-box;" value="<?php echo $query; ?>" />
            </label>
        <input type="hidden" name="title" value="Special:search" />
        <input type="submit" value="Search" />
    </div>
</form>
<?php 
if (!$query) {
    return;
}
?>
<h2>You may be looking for...</h2>
<ul>
<?php 
$results = array();
$ids = (array) json_decode(file_get_contents(__DIR__ . "/../pages/page2ID.json"));
$terms = explode(" ", $query);
require_once __DIR__ . '/../markdown/parsedown/parsedown.php';
$Parsedown = new Parsedown;
foreach ($ids as $page => $id) {
    $pageName = $page;
    $matches = custom_substr_count($terms, $pageName);
    $matches += custom_substr_count($terms, strip_tags($Parsedown->text(file_get_contents(__DIR__ . "/../pages/data/$id/page.md"))));
    if (!$matches) continue;
    $history = json_decode(file_get_contents(__DIR__ . "/../pages/data/$id/revisions.json"));
    $result = new stdClass;
    $result->title = $page;
    $result->matches = $matches;
    $result->lastEd = $history[0]->time;
    $result->lastEdBy = $history[0]->author;
    array_push($results, $result);
}
usort($results, function($a, $b) {
    return $b->matches - $a->matches;
});
if (!count($results)) {
    ?><li>Sorry. Your search matched no results.</li><?php
}
foreach ($results as $result) {
    ?><li><h3><a href="index.php?title=<?php echo htmlspecialchars(urlencode($result->title)); ?>"><?php echo htmlspecialchars($result->title); ?></a></h3>
    <?php echo $result->matches; ?> match<?php if ($result->matches > 1) { ?>es<?php } ?>,
    last edited by <?php echo userlink(userinfo($result->lastEdBy)->username); ?> on 
    <?php echo formatDate($result->lastEd); ?>
    </li><?php
}
function custom_substr_count(array $terms, string $matchThis) {
    $count = 0;
    foreach ($terms as $term) {
        $count += substr_count(strtolower($matchThis), strtolower($term));
    }
    return $count;
}
?>
</ul>