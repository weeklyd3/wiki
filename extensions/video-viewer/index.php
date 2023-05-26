<?php
add_special_page("GetVideoViewerScript", __DIR__ . "/script.php");
add_special_page("GetVideoViewerStyle", __DIR__ . "/stylesheet.php");
add_script("index.php?title=Special:GetVideoViewerScript");
add_stylesheet('index.php?title=Special:GetVideoViewerStyle');