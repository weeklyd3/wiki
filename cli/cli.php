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
 require 'tool.php';
function cleanFilename($stuff) {
	$illegal = array(" ","?","/","\\","*","|","<",">",'"');
	$legal = array("-","_","_","_","_","_","_","_","_");
	$cleaned = str_replace($illegal,$legal,$stuff);
	return $cleaned;
}
if (!isset($argv[1])) exit("There is no module provided to run.\n");
$module = cleanFilename($argv[1]);
echo "wiki CLI (c) 2022 weeklyd3. May be distributed under the terms of the GNU GPL.\n";
echo "Module to run: $module\n";
if (!file_exists(__DIR__ . "/$module.php")) die("Fatal: Module not found. Stopping.\n");
echo "Running module $module...\n";
require "$module.php";