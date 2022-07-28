Adding license headers...
<?php
$skip = array("extraload.js", "load.js", "markdown/htmlpurifier/", "markdown/parsedown/parsedown_old.php", "markdown/parsedown/parsedown.php", "markdown/parsedown/LICENSE.txt", "LICENSE", "footer.html", '.json', 'highlight-js', '.md', '.git', '.gitignore', 'optionsWarning');
$formats = array(
    "php" => array("start" => "<?php\n/*", "end" => "*/\n?>"),
    "css" => array("start" => "/*", "end" => "*/")
);
$licensetext = <<<TEXT
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
TEXT
?>
Files to skip:
<?php 
foreach ($skip as $s) echo "* $s\n";
$count = 0;
function scan($dir = __DIR__ . "/..") {
    $installPath = realpath('..');
    global $count;
    global $skip;
    global $formats;
    global $licensetext;
    $items = scandir($dir, SCANDIR_SORT_NONE);
    $actualItems = array();
    foreach ($items as $item) {
        $olditem = $item;
        $item = realpath("$dir/$item");
        $relative = substr($item, strlen($installPath));
        if ($olditem === '.' || $olditem === '..') continue;
        if (is_dir("$dir/$olditem")) {
            scan("$dir/$olditem");
            continue;
        }
        echo "Adding headers to items: #$count being processed\n";
        echo "Add to $item\n";
        if (in_array($relative, $skip)) {
            echo "$relative matched $skip on the skip list.\n";
            continue;
        }
        foreach ($skip as $sk) {
            if (strpos($relative, $sk) !== false) {
                echo "$relative indirectly matches item $sk on the skip list. Skipping.\n";
                continue 2;
            }
        }
        echo "Detecting file type... ";
        $filetype = explode('.', $olditem)[count(explode('.', $olditem)) - 1];
        echo $filetype;
        echo "\n";
        if (!isset($formats[$filetype])) {
            echo "Comment format unknown. Skipping.\n";
            continue;
        }
        echo "Writing file...\n";
        $contents = file_get_contents($item);
        $oldcontents = $contents;
        if (strpos($contents, $licensetext) === false) {
            $contents = "{$formats[$filetype]['start']}\n{$licensetext}\n{$formats[$filetype]['end']}\n{$contents}";
            // some cleanup
            $contents = str_replace("?>\n<?php", "", $contents);
            fwrite(fopen($item, "w+"), $contents);
        }
        $count++;
    }
}
$files = scan();
?>