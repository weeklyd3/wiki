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
 
$title = 'Search results';
$query = $_GET['query'] ?? '';
require_once __DIR__ . '/../accounts.php';
require_once __DIR__ . '/../date.php';
?>
<form action="index.php" method="GET">
            <label><span class="hidden2eyes"><?php echo sysmsg('search-query'); ?> </span>
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
<p>Go to the page <a href="index.php?title=<?php echo htmlspecialchars(urlencode($query)); ?>"><?php echo htmlspecialchars($query); ?></a>?</p>
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