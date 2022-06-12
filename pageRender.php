<?php
function cleanFilename($stuff) {
	$illegal = array(" ","?","/","\\","*","|","<",">",'"');
	$legal = array("-","_","_","_","_","_","_","_","_");
	$cleaned = str_replace($illegal,$legal,$stuff);
	return $cleaned;
}
function renderPage(string $namespace, string $title) {
    global $title;
    switch ($namespace) {
        case 'Special':
            $specialPageFileName = cleanFilename(substr($title, 8));
            if (!file_exists("special/$specialPageFileName.php")) $specialPageFileName = 'invalid';
            $pageName = $title;
            $pageNamespace = $namespace;
            require "special/$specialPageFileName.php";
            break;
        default:
            require __DIR__ . '/markdown/parsedown/parsedown.php';
            $Parsedown = new Parsedown;
            $pageIndex = json_decode(file_get_contents("pages/page2ID.json"));
            if (!isset($pageIndex->$title)) {
                echo $Parsedown->text(str_replace(array('$1', '$2'), array($title, urlencode($title)), file_get_contents('nosuchpage.txt')));
            } else {
                $id = $pageIndex->$title;
                echo $Parsedown->text(file_get_contents("pages/data/$id/page.md"));
            }
            break;
    }
}