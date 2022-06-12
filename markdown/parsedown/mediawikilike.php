<?php 
function parseMediawiki(string $text) {
    $text = wikilinks($text);
    return $text;
}
function wikilinks(string $text) {
    return preg_replace_callback('/\[\[(.*?)\]\]/i', function($match) {
        $match = $match[1];
        $e = explode('|', $match);
        if (count($e) > 1) $text = $e[count($e) - 1];
        else $text = $match;
        if (count($e) === 1) $pagename = $match;
        else $pagename = implode('|', array_slice($e, 0, count($e) - 1));
        return '<a href="index.php?title=' . htmlspecialchars(urlencode($pagename)) . '">' . $text . '</a>';
    }, $text);
}