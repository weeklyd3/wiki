<?php
global $scripts;
$scripts = array('load.js', 'extraload.js');
function add_script(string $url) {
    global $scripts;
    array_push($scripts, $url);
}
global $stylesheets;
$stylesheets = array('style.css', 'highlight-js/vs.min.css');
function add_stylesheet(string $url) {
    global $stylesheets;
    array_push($stylesheets, $url);
}