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

ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', 'true');
ob_start();
session_start();
require 'scripts.php';
require_once 'pages.php';
require_once 'settings.php';
require_once 'accounts.php';
require_once 'date.php';
global $useTemplates;
if (!isset($_GET['title'])) header("Location: index.php?title=Main Page");
$title = $_GET['title'] ?? 'Main Page';
if (!defined('installed') && ($_GET['title'] ?? 'poo poo') !== 'Special:install') {
    header('Location: index.php?title=Special:install');
    $title = 'Special:install';
}
$originalPageName = $title;
$action = isset($_GET['action']) ? $_GET['action'] : "";
$deletedPageIndex = json_decode(file_get_contents(__DIR__ . "/deleted-pages/page2IDs.json"));
if (!isset($deletedPageIndex->$originalPageName)) $previouslyDeleted = false;
else $previouslyDeleted = true;
require_once 'pageRender.php';
if (substr($originalPageName, 0, strlen('User talk:')) === 'User talk:' && isset($_SESSION['username'])) { 
    $userTalkPageName = substr($originalPageName, strlen('User talk:'));
    $currentUserID = $_SESSION['userid'];
    if (file_exists(__DIR__ . "/users/data/$currentUserID/newMessages.json") && $userTalkPageName === $_SESSION['username']) unlink(__DIR__ . "/users/data/$currentUserID/newMessages.json");
}
$redirectFrom = false;
// You are not expected to understand that.
$redirectRegex = '/#REDIRECT \[\[(.*?)\]\]/i';
$pageIndex = json_decode(file_get_contents("pages/page2ID.json"));
if (isset($pageIndex->$originalPageName) && !isset($_GET['action'])) {
    $id = $pageIndex->$originalPageName;
    $text = file_get_contents("pages/data/$id/page.md");
    $matched = preg_match($redirectRegex, explode("\n", $text)[0], $targetPage);
    if ($matched === 1) {
        if (!isset($_GET['noredirect'])) {
            $o = $originalPageName;
            $originalPageName = $targetPage[1];
            $title = $originalPageName;
            $utitle = urlencode($title);
            error_log('Redirecting page to ' . $title);
            $redirectFrom = $o;
        }
    }
}
if (isset($_GET['raw'])) {
    renderPage(explode(":", $title)[0], $title);
    exit(0);
}
if (!$action) renderPage(explode(":", $title)[0], $title);
else {
    switch ($action) {
        case 'history':
            $title = "Page history for $originalPageName";
            $pageIndex = json_decode(file_get_contents("pages/page2ID.json"));
            if (!isset($pageIndex->$originalPageName)) {
                ?><p>This page does not exist. There is no history for it.</p><?php
                return;
            }
            $id = $pageIndex->$originalPageName;
            $rev = json_decode(file_get_contents("pages/data/$id/revisions.json"));
            ?>
            <ul>
                <?php foreach ($rev as $index => $r) { 
                $authorinfo = userinfo($r->author);
                ?>
                <li>(<a href="index.php?title=Special:old&page=<?php echo htmlspecialchars(urlencode($originalPageName)); ?>&revision=<?php echo $r->id; ?>">archive</a>) <?php echo formatDate($r->time); ?> by <?php echo userlink($authorinfo->username); ?> (<?php echo htmlspecialchars($r->summary); ?>)
                <?php if (isset($rev[$index - 1])) {
                    ?><span class="restore-link-brackets">[</span><a class="restore-link"
                        href="index.php?title=Special:rollback&page=<?php echo htmlspecialchars(urlencode($originalPageName)); ?>&revision=<?php echo count($rev) - $index - 1; ?>"><?php echo sysmsgPlain('restore-this-version'); ?></a><span class="restore-link-brackets">]</span><?php
                } ?></li>
                <?php } ?>
            </ul>
            <?php
            break;
        case 'edit':
            if (!canEditPage($originalPageName)[0]) {
                echo sysmsg("cant-edit-" . canEditPage($originalPageName)[1], htmlspecialchars($originalPageName));
                echo sysmsg('can-still-view-source');
                ?><pre><code><?php echo htmlspecialchars(page_get_contents($originalPageName)); ?></code></pre><?php
                break;
            }
            $pageIndex = json_decode(file_get_contents("pages/page2ID.json"));
            if (!isset($pageIndex->$originalPageName)) {
                echo sysmsg('page-does-not-exist-header');
                if ($previouslyDeleted) displayDeleteLog("Notice: This page has been previously deleted. Please make sure you aren't creating anything that's deletable under the following");
            }
            $id = $pageIndex->$originalPageName;
            $title = "$originalPageName - edit";
            if (!isset($_POST['preview'])) {
                echo sysmsg('edit-header', htmlspecialchars($originalPageName));
            } else {
                echo sysmsg('preview');
                require_once __DIR__ . '/markdown/parsedown/parsedown.php';
                $Parsedown = new Parsedown;
                echo $Parsedown->text($_POST['contents']);
            } 
            if (isset($_POST['save'])) {
                modifyPage($originalPageName, $_POST['contents'], $_POST['summary']);
                if (substr($originalPageName, 0, strlen('User talk:')) === 'User talk:') {
                    $userIDs = json_decode(file_get_contents(__DIR__ . "/users/name2ID.json"));
                    $user2contact = substr($originalPageName, strlen('User talk:'));
                    if (isset($userIDs->$user2contact)) {
                        $userID2Contact = $userIDs->$user2contact;
                        fwrite(fopen(__DIR__ . "/users/data/$userID2Contact/newMessages.json", "w+"), 'true');
                    }
                }
                header('Location: index.php?title=' . urlencode($originalPageName));
                exit(0);
            }
            ?>
            <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
                <label>
                    <?php echo strip_tags(sysmsg('edit-box-label')); ?><div></div>
                    <textarea style="box-sizing: border-box; width: 100%;" rows="10" name="contents"><?php if (isset($_POST['contents'])) echo htmlspecialchars($_POST['contents']); else echo htmlspecialchars(file_get_contents("pages/data/$id/page.md")); ?></textarea>
                </label>
                <div></div>
                <label>
                    <?php echo strip_tags(sysmsg('edit-summary-label')); ?><div></div>
                    <input name="summary" style="box-sizing: border-box; width: 100%;" type="text" value="<?php echo isset($_POST['summary']) ? htmlspecialchars($_POST['summary']) : ""; ?>" />
                </label>
                <div></div>
                <input type="submit" name="save" value="Save page" />
                <input type="submit" name="preview" value="Preview page" />
            </form>
            <?php
            break;
        default:
            ?><?php echo sysmsg('invalid-action-parameter', htmlspecialchars($action)); ?><?php
            break;
    }
}
$output = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width,initial-scale=1.0" />
        <title><?php echo htmlspecialchars($title); ?></title>
        <?php
        foreach ($stylesheets as $stylesheet) {
            ?><link rel="stylesheet" href="<?php echo htmlspecialchars($stylesheet); ?>" /><?php
            echo "\n";
        }
        ?>
        <script src="highlight-js/highlight.min.js"></script>
        <script>const config = <?php 
        $config = new stdClass;
        $config->loggedIn = isset($_SESSION['username']);
        $config->username = $_SESSION['username'] ?? null;
        $config->userid = $_SESSION['userid'] ?? null;
        $config->userGroups = isset($_SESSION['userid']) ? getUserGroups($_SESSION['userid']) : array();
        $config->userGroupsSimple = isset($_SESSION['userid']) ? getUserGroups($_SESSION['userid'], true) : array();
        $config->originalPageName = $originalPageName;
        echo json_encode($config, 128);
        ?>; window.config = config;</script>
        <?php 
        foreach ($scripts as $url) {
            ?><script src="<?php echo htmlspecialchars($url); ?>"></script><?php
            echo "\n";
        }
        
        if ($redirectFrom !== false) { 
            ?><link rel="canonical" href="index.php?title=<?php echo htmlspecialchars(urlencode($originalPageName)); ?>" /><?php
        } ?>
    </head>
    <body id="baudy">
        <script>const pageTitle = <?php echo json_encode($originalPageName); ?>;</script>
        <?php require_once 'header.php'; ?>
        <h1 id="taitl"><?php echo htmlspecialchars($title); ?></h1>
        <small id="subheading"><?php echo htmlspecialchars($subheading); ?></small>
        <?php 
        if ($redirectFrom !== false) {
            ?><div><small id="rdrfrom"><?php echo sysmsg('redirected', '<a href="index.php?title=' . htmlspecialchars(urlencode($redirectFrom)) . '&noredirect">' . htmlspecialchars($redirectFrom) . '</a>'); ?></small>
        <script>
        const actualPageName = <?php echo json_encode($originalPageName); ?>;
        const url = new URL(location.href);
        url.searchParams.set('title', actualPageName);
        window.history.replaceState({}, document.title, url);</script>
        </div><?php
        }
        ?>
        <div id="page-content"><?php echo $output; ?></div>
        <footer><?php require_once 'footer.html'; ?></footer>
    </body>
</html>