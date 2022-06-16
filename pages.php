<?php
class revision {
    public function __construct(string $summary, int $id) {
        $this->time = time();
        $this->author = $_SESSION['userid'];
        $this->summary = $summary;
        $this->id = $id;
    }
}
function modifyPage(string $name, string $contents, string $editSummary): bool {
    if (!isset($_SESSION['username'])) return false;
    $currentuser = $_SESSION['username'];
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
            $entries = queryLog(array('delete', 'undelete'), $originalPageName, false);
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