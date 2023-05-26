<?php
header('Content-Type: text/javascript');
echo str_replace(array(), array(), file_get_contents('viewer.js'));
exit();