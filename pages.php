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

class revision {
    public function __construct(string $summary, int $id) {
        $this->time = time();
        $this->author = $_SESSION['userid'];
        $this->summary = $summary;
        $this->id = $id;
    }
}
require_once __DIR__ . "/log/log.php";
function modifyPage(string $name, string $contents, string $editSummary): bool {
    if (!isset($_SESSION['username'])) return false;
    if (!canEditPage($name)[0]) return false;
    $currentuser = $_SESSION['username'];
    require_once __DIR__ . "/date.php";
    $contents = str_replace("~~~~", "<span class=\"signature\">[[User:$currentuser|$currentuser]] ([[User talk:$currentuser|Leave this user a message]]) " . formatDate(time()) . '</span>', $contents);
    $page2ID = json_decode(file_get_contents(__DIR__ . '/pages/page2ID.json'));
    if (!isset($page2ID->$name)) {
        $currentID = json_decode(file_get_contents(__DIR__ . '/pages/currentPageID.json'));
        $page2ID->$name = $currentID;
        fwrite(fopen(__DIR__ . '/pages/page2ID.json', 'w+'), json_encode($page2ID));
        fwrite(fopen(__DIR__ . '/pages/currentPageID.json', 'w+'), $currentID + 1);
        mkdir(__DIR__ . "/pages/data/$currentID", 0777, true);
        mkdir(__DIR__ . "/pages/data/$currentID/pastRevisions", 0777, true);
        fwrite(fopen(__DIR__ . "/pages/data/$currentID/currentRevisionID.json", "w+"), 0);
    }
    $id = $page2ID->$name;
    $revisionID = json_decode(file_get_contents(__DIR__ . "/pages/data/$id/currentRevisionID.json"));
    fwrite(fopen(__DIR__ . "/pages/data/$id/currentRevisionID.json", "w+"), $revisionID + 1);
    mkdir(__DIR__ . "/pages/data/$id/pastRevisions/$revisionID", 0777, true);
    fwrite(fopen(__DIR__ . "/pages/data/$id/pastRevisions/$revisionID/page.md", "w+"), $contents);
    fwrite(fopen(__DIR__ . "/pages/data/$id/page.md", "w+"), $contents);

    $revisionDataExists = file_exists(__DIR__ . "/pages/data/$id/revisions.json");
    if ($revisionDataExists) $revisions = json_decode(file_get_contents(__DIR__ . "/pages/data/$id/revisions.json"));
    else $revisions = array();
    array_unshift($revisions, new revision($editSummary, $revisionID));
    fwrite(fopen(__DIR__ . "/pages/data/$id/revisions.json", 'w+'), json_encode($revisions));
    $newEdit = new stdClass;
    $newEdit->pageID = $id;
    $newEdit->title = $name;
    $newEdit->summary = $editSummary;
    $newEdit->author = $_SESSION['userid'];
    $newEdit->size = strlen($contents);
    $newEdit->revid = $revisionID;
    $newEdit->time = time();
    $newEdits = json_decode(file_get_contents(__DIR__ . "/mod/recentChanges.json"));
    array_unshift($newEdits, $newEdit);
    fwrite(fopen(__DIR__ . "/mod/recentChanges.json", 'w+'), json_encode($newEdits));
    new logEntry($_SESSION['userid'], null, $name, "edit", "Edited page $name", "Edit summary: $editSummary");
    return true;
}
function deletePage(string $title, bool $deleteFileIfExists = true): ?bool {
    if (!isset($_SESSION['userid'])) {
        return null;
    }
    $ug = getUserGroups($_SESSION['userid'], true);
    global $adminUserGroup;
    if (!in_array($adminUserGroup, $ug)) return null;
    $pageIndex = json_decode(file_get_contents((__DIR__ . "/pages/page2ID.json")));
    if (!isset($pageIndex->$title)) return false;
    $id = $pageIndex->$title;
    rename(__DIR__ . "/pages/data/$id", __DIR__ . "/deleted-pages/data/$id");
    unset($pageIndex->$title);
    fwrite(fopen(__DIR__ . "/pages/page2ID.json", "w+"), json_encode($pageIndex));
    $deletedPageIndex = json_decode(file_get_contents(__DIR__ . "/deleted-pages/page2IDs.json"));
    if (!isset($deletedPageIndex->$title)) $deletedPageIndex->$title = array();
    array_push($deletedPageIndex->$title, $id);
    fwrite(fopen(__DIR__ . "/deleted-pages/page2IDs.json", "w+"), json_encode($deletedPageIndex));
    if (substr($title, 0, 5) === 'File:' && $deleteFileIfExists) {
        // Delete the file as well
        $filename = cleanFilename(substr($title, 5));
        if (file_exists(__DIR__ . "/files/live/$filename")) unlink(__DIR__ . "/files/live/$filename");
    }
    return true;
}
function displayDeleteLog(string $before): void {
    global $originalPageName;
    ?>
    <div class="error">
        <?php echo $before; ?>
        <table>
            <tr>
                <th>Username</th>
                <th>Time</th>
                <th>Type</th>
                <th>Comment</th>
                <th>Reason</th>
            </tr>
            <?php 
            require_once __DIR__ . "/log/log.php";
            $entries = queryLog(array('delete', 'undelete', 'protect'), $originalPageName, false);
            foreach ($entries as $entry) {
                ?>
                    <tr>
                        <td><?php echo userlink(userinfo($entry->performer)->username); ?></td>
                        <td><?php echo formatDate($entry->time); ?></td>
                        <td><?php echo htmlspecialchars($entry->type); ?></td>
                        <td><?php echo htmlspecialchars($entry->comment); ?></td>
                        <td><?php echo htmlspecialchars($entry->reason); ?></td>
                    </tr>
                <?php
            }
            ?>
        </table>
    </div>
    <?php
}
/**
 * Checks if a page exists.
 * 
 * This function checks if a page exists and was not deleted. 
 * 
 * @return bool If the page exists.
 * @param string The name of the page.
 */
function page_exists(string $title): bool {
    $page2ID = json_decode(file_get_contents(__DIR__ . "/pages/page2ID.json"));
    return isset($page2ID->$title);
}
function canEditPage(string $pageName): array {
    global $adminUserGroup;
    if (!isset($_SESSION['userid'])) return array(false, 'loginRequired');
    if (substr($pageName, 0, strlen('Interface:')) === 'Interface:' && !in_array($adminUserGroup, getUserGroups($_SESSION['userid'] ?? '', true))) return array(false, 'interfaceProtected');
    $protection = json_decode(file_get_contents("protect.json"));
    if (in_array($pageName, $protection) && !in_array($adminUserGroup, getUserGroups($_SESSION['userid'] ?? '', true))) return array(false, 'adminProtected');
    return array(true);
}
function sysmsg(string $messageName, ...$args): string {
    require_once __DIR__ . "/markdown/parsedown/parsedown.php";
    $Parsedown = new Parsedown;
    return $Parsedown->text(sysmsg_raw($messageName, ...$args));
}
function sysmsgPlain(string $messageName, ...$args): string {
    return htmlspecialchars(sysmsg_raw($messageName, ...$args));
}
$defaultSystemMessages = json_decode(file_get_contents(__DIR__ . "/defaultinterface.json"));
function sysmsg_raw(string $messageName, ...$args): string {
    if (isset($_GET['messagenames'])) return "(Interface:$messageName)";
    if (page_get_contents("Interface:$messageName")) {
        $text = page_get_contents("Interface:$messageName");
        foreach (array_reverse($args, true) as $index => $arg) {
            $indexplusone = $index + 1;
            $text = str_replace("$$indexplusone", $arg, $text);
        }
        return $text;
    }
    global $defaultSystemMessages;
    if (isset($defaultSystemMessages->$messageName)) {
        $text = $defaultSystemMessages->$messageName;
        foreach (array_reverse($args, true) as $index => $arg) {
            $indexplusone = $index + 1;
            $text = str_replace("$$indexplusone", $arg, $text);
        }
        return $text;
    }
    return "System message not found: $messageName";
}
function page_get_contents(string $pagename): ?string {
    $page2ID = json_decode(file_get_contents(__DIR__ . "/pages/page2ID.json"));
    if (!isset($page2ID->$pagename)) return null;
    return file_get_contents(__DIR__ . "/pages/data/" . $page2ID->$pagename . "/page.md");
}
global $specialPages;
$specialPages = array();
foreach (scandir("special/", SCANDIR_SORT_NONE) as $page) {
    if ($page === "." || $page === '..') continue;
    add_special_page(substr($page, 0, -4), __DIR__ . "/special/$page");
}
function add_special_page($name, $filename) {
    global $specialPages;
    $specialPages[$name] = $filename;
}
function special_page_exists(string $specialPage): bool {
    global $specialPages;
    return isset($specialPages[$specialPage]);
}
function loadExtension(string $extname) {
    if (!is_dir(__DIR__ . "/extensions/$extname")) {         
        echo sysmsg('invalid-extension', $extname);
        return;
    }
    if (!file_exists(__DIR__ . "/extensions/$extname/index.php")) {
        echo sysmsg('malformed-extension', $extname);
        return;
    }
    require __DIR__ . "/extensions/$extname/index.php";
}
function add_system_messages(string $filename): bool {
    global $defaultSystemMessages;
    if (!file_exists($filename)) return false;
    $messages = json_decode(file_get_contents($filename));
    if (!$messages) return false;
    $messages = (array) $messages;
    foreach ($messages as $name => $message) {
        $defaultSystemMessages->$name = $message;
    }
    return true;
}