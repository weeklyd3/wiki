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

if (php_sapi_name() !== "cli") {
    exit('Please run this from the CLI SAPI. Like, php ' . __FILE__);
}
require_once 'parser.php';
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
TEST 6 - Nested templates
<?php 
$wikitext = "{{template{{bar}}{{baz}}|foo{{baz}}}}";
echo parse($wikitext, function($title) {
    return "{Template $title}";
});