<?php
if (php_sapi_name() !== "cli") {
    exit('Please run this from the CLI SAPI. Like, php ' . __FILE__);
}
require 'parser.php';
?>
TEST 1 - Simple template
========================
<?php 
$wikitext = '{{test}}';
var_dump(parseTemplates($wikitext));
?> 
TEST 2 - Nesting
================
<?php 
$wikitext = '{{test{{nested{{template}}}}}}';
var_dump(parseTemplates($wikitext));
?>
TEST 3 - Actual parser 
======================
<?php 
$wikitext = '{{template}}';
echo parse($wikitext, function($title) {
    if ($title === 'Template:nest') return 'nested';
    else return '{{nest}}';
});
?>

TEST 4 - Actual arguments
=========================
<?php 
$wikitext = '{{template|text}}';
echo parse($wikitext, function($title, ...$args) {
    return "Template. Argument 1: {$args[0]}";
});
?>

TEST 5 - MALFORMED TEMPLATE SYNTAX
==================================
TEST 5.1 - Not 2 braces
========================
<?php 
$wikitext = '{notatemplate}';
echo parse($wikitext, function($title) {
    return "Template $title";
});
echo "\n";
$wikitext = '{{{thismaybeatemplate}}}';
echo parse($wikitext, function($title) {
    return "Template $title";
});
?> 

TEST 5.2 - Opening and closing braces don't match 
=================================================
<?php 
$wikitext = '{{{3open2close}}';
echo parse($wikitext, function($title) {
    return "Template $title";
});
echo "\n";
?>