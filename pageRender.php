<?php
global $previouslyDeleted;
function cleanFilename($stuff) {
	$illegal = array(" ","?","/","\\","*","|","<",">",'"');
	$legal = array("-","_","_","_","_","_","_","_","_");
	$cleaned = str_replace($illegal,$legal,$stuff);
	return $cleaned;
}
function renderPage(string $namespace, string $title) {
    global $title;
    global $previouslyDeleted;
    switch ($namespace) {
        case 'Special':
            $specialPageFileName = cleanFilename(substr($title, 8));
            if (!file_exists("special/$specialPageFileName.php")) $specialPageFileName = 'invalid';
            $pageName = $title;
            $pageNamespace = $namespace;
            require "special/$specialPageFileName.php";
            break;
        case "File":
            // Note the intentional absence of the break statement. This is required to
            // render the description page below the file.
            $filename = cleanFilename(substr($title, 5));
            if (!file_exists(__DIR__ . "/files/live/$filename")) {
                ?><div class="error">There seems to be no file with that name. If you would like to <a href="index.php?title=Special:upload">upload it</a>, go ahead.</div><?php
            } else {
                ?><div id="filedesc">
                    A local file exists.
                    <div id="stats"><?php echo htmlspecialchars($filename); ?> (<?php echo filesize(__DIR__ . "/files/live/$filename"); ?> bytes), <a href="index.php?title=Special:rawfile&filename=<?php echo htmlspecialchars(urlencode($filename)); ?>">download</a></div>
                    <div>Use this file on-wiki: <code>![alt/caption text](<?php echo htmlspecialchars($filename); ?>)</code></div>
                    <div><em>(The local file description page follows.)</em></div>
                </div>
                <hr /><?php
            }
        default:
            require __DIR__ . '/markdown/parsedown/parsedown.php';
            $Parsedown = new Parsedown;
            $pageIndex = json_decode(file_get_contents("pages/page2ID.json"));
            if (!isset($pageIndex->$title)) {
                if ($previouslyDeleted) displayDeleteLog("This page does not exist, but was previously deleted:");
                echo $Parsedown->text(str_replace(array('$1', '$2'), array(htmlspecialchars($title), htmlspecialchars(urlencode($title))), file_get_contents('nosuchpage.txt')));
            } else {
                $id = $pageIndex->$title;
                echo $Parsedown->text(file_get_contents("pages/data/$id/page.md"));
            }
            break;
    }
}