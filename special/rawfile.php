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
 
require_once __DIR__ . "/../pageRender.php";
$filename = cleanFilename($_GET['filename'] ?? 'example.txt');
if (!file_exists(__DIR__ . "/../files/live/$filename")) die('File not found');
$type = mime_content_type(__DIR__ . "/../files/live/$filename");
header('Content-Type: ' . $type);
header('Content-Disposition: inline; filename="' . $filename . '"');
readfile(__DIR__ . "/../files/live/$filename");
exit(0);