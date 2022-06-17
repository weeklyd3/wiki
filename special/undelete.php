<?php
$groups = getUserGroups($_SESSION['userid'], true);
require_once __DIR__ . "/../settings.php";
global $adminUserGroup;
if (!in_array($adminUserGroup, $groups)) {
    $title = 'Permission denied';
    ?><p>You need administrator access to undelete pages. Sorry.</p><?php
    return;
}
if (!isset($_GET['page'])) {
    $title = 'Nothing to restore';
    ?><p>You need to specify something to take out of the trash.</p><?php
    return;
}
$deletedPageIndex = json_decode(file_get_contents(__DIR__ . "/../deleted-pages/page2IDs.json"));
$page = $_GET['page'];
if (!isset($deletedPageIndex->$page)) {
    $title = 'You cannot undelete this';
    ?><p>Perhaps the page was never created or deleted in the first place.</p><?php 
    return;
}
$title = "Restore $page";
if (isset($_POST['restore'])) {
    $indexes = $deletedPageIndex->$page;
    if (!in_array($_POST['version'], $indexes)) {
        ?><div class="error">The selected page was not found in the deleted page archive.</div><?php
    } else {
        require_once __DIR__ . "/../log/log.php";
        new logEntry($_SESSION['userid'], null, $page, 'undelete', $_SESSION['username'] . " undeleted page $page", $_POST['reason']);
        $delid = $_POST['version'];
        rename(__DIR__ . "/../deleted-pages/data/$delid", __DIR__ . "/../pages/data/$delid");
        $indexes = array_filter($indexes, function($value) use ($delid) {
            return $value != $delid;
        });
        $deletedPageIndex->$page = $indexes;
        if (count($indexes) === 0) unset($deletedPageIndex->$page);
        fwrite(fopen(__DIR__ . "/../deleted-pages/page2IDs.json", "w+"), json_encode($deletedPageIndex));
        $pageIndex = json_decode(file_get_contents(__DIR__ . "/../pages/page2ID.json", "w+"));
        $pageIndex->$page = (int) $delid;
        fwrite(fopen(__DIR__ . "/../pages/page2ID.json", "w+"), json_encode($pageIndex));
        $title = 'Undelete successful';
        ?><p>The page has been restored.</p><?php
        return;
    }
}
?>
<p>Using this form, you can restore a version of a deleted page. Please choose the correct version to restore!</p>
<?php 
if (isset($_POST['preview'])) {
    $indexes = $deletedPageIndex->$page;
    if (!in_array($_POST['version'], $indexes)) {
        ?><div class="error">The selected page was not found in the deleted page archive.</div><?php
    } else {
        $deleteID = $_POST['version'];
        $revs = json_decode(file_get_contents(__DIR__ . "/../deleted-pages/data/$deleteID/revisions.json"));
        ?>
        <fieldset>
            <legend>Preview of page to restore (<?php echo count($revs); ?> revision<?php if (count($revs) !== 1) { ?>s<?php } ?>)</legend>
            <?php 
            require __DIR__ . "/../markdown/parsedown/parsedown.php";
            $Parsedown = new Parsedown;
            echo $Parsedown->text(file_get_contents(__DIR__ . "/../deleted-pages/data/$deleteID/page.md"));
            ?>
        </fieldset>
        <?php
    }
}
?>
<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
    <fieldset>
        <legend>
            Version to restore:
        </legend>
        <ul>
            <?php 
            $versions = $deletedPageIndex->$page;
            foreach ($versions as $version) {
                ?><li><label><input <?php 
                if (($_POST['version'] ?? '') == $version) {
                    ?>checked="checked" <?php
                }
                ?>type="radio" name="version" value="<?php echo $version; ?>" /> Version <?php echo $version; ?></label></li><?php
            }
            ?>
        </ul>
    </fieldset>
    <label>Reason for undelete: <input name="reason" value="<?php echo htmlspecialchars($_POST['reason'] ?? ''); ?>" required="required" /></label><div></div>
    <input name="preview" type="submit" value="Show deleted page" />
    <button name="restore">
        Restore page
    </button>
</form>