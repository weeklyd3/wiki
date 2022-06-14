<?php 
require_once __DIR__ . "/../pageRender.php";
$filename = cleanFilename($_GET['filename'] ?? 'example.txt');
if (!file_exists(__DIR__ . "/../files/live/$filename")) die('File not found');
$type = mime_content_type(__DIR__ . "/../files/live/$filename");
header('Content-Type: ' . $type);
header('Content-Disposition: inline; filename="' . $filename . '"');
readfile(__DIR__ . "/../files/live/$filename");
exit(0);