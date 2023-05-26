<?php
header('Content-Type: text/javascript');
echo file_get_contents(__DIR__ . '/viewer.js');
exit();