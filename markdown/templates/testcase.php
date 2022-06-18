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