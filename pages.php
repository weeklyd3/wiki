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
    return true;
}