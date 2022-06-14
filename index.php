<?php
ob_start();
session_start();
require 'settings.php';
require 'accounts.php';
require 'pages.php';
require 'date.php';
if (!isset($_GET['title'])) header("Location: index.php?title=Main Page");
$title = $_GET['title'] ?? 'Main Page';
if (!defined('installed') && ($_GET['title'] ?? 'poo poo') !== 'Special:install') {
    header('Location: index.php?title=Special:install');
    $title = 'Special:install';
}
$originalPageName = $title;
$action = isset($_GET['action']) ? $_GET['action'] : "";

require 'pageRender.php';
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
            }
            $id = $pageIndex->$originalPageName;
            $rev = json_decode(file_get_contents("pages/data/$id/revisions.json"));
            ?>
            <ul>
                <?php foreach ($rev as $r) { 
                $authorinfo = userinfo($r->author);
                ?>
                <li>(<a href="index.php?title=Special:old&page=<?php echo htmlspecialchars(urlencode($originalPageName)); ?>&revision=<?php echo $r->id; ?>">archive</a>) <?php echo formatDate($r->time); ?> by <?php echo userlink($authorinfo->username); ?> (<?php echo htmlspecialchars($r->summary); ?>)</li>
                <?php } ?>
            </ul>
            <?php
            break;
        case 'edit':
            if (!isset($_SESSION['username'])) {
                ?><p>Please log in to edit pages.</p><?php
                break;
            }
            $pageIndex = json_decode(file_get_contents("pages/page2ID.json"));
            if (!isset($pageIndex->$originalPageName)) {
                ?><p>Notice: This page does not exist yet. Saving your edits will create it.</p><?php
            }
            $id = $pageIndex->$originalPageName;
            $title = "$originalPageName - edit";
            if (!isset($_POST['preview'])) {
            ?>
            <p>You are currently editing <?php echo htmlspecialchars($originalPageName); ?>. Once you publish your changes, they will be publicly viewable immediately. Do not abuse this privilege.</p>
            <?php } else {
                ?><p>Below is a preview. Your page has not been saved yet.</p><?php
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
                    Page contents:<div></div>
                    <textarea style="box-sizing: border-box; width: 100%;" rows="10" name="contents"><?php if (isset($_POST['contents'])) echo htmlspecialchars($_POST['contents']); else echo htmlspecialchars(file_get_contents("pages/data/$id/page.md")); ?></textarea>
                </label>
                <div></div>
                <label>
                    Briefly describe what you changed:<div></div>
                    <input name="summary" style="box-sizing: border-box; width: 100%;" type="text" value="<?php echo isset($_POST['summary']) ? htmlspecialchars($_POST['summary']) : ""; ?>" />
                </label>
                <div></div>
                <input type="submit" name="save" value="Save page" />
                <input type="submit" name="preview" value="Preview page" />
            </form>
            <?php
            break;
        default:
            ?><p>Invalid action parameter. Sorry.</p><?php
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
        <link rel="stylesheet" href="style.css" />
        <script src="highlight-js/highlight.min.js"></script>
        <link rel="stylesheet" href="highlight-js/vs.min.css" />
        <script src="load.js"></script>
        <?php if ($redirectFrom !== false) { 
            ?><link rel="canonical" href="index.php?title=<?php echo htmlspecialchars(urlencode($originalPageName)); ?>" /><?php
        } ?>
    </head>
    <body id="baudy">
        <?php require 'header.php'; ?>
        <h1 id="taitl"><?php echo htmlspecialchars($title); ?></h1>
        <small id="subheading"><?php echo htmlspecialchars($subheading); ?></small>
        <?php 
        if ($redirectFrom !== false) {
            ?><div><small id="rdrfrom">Redirected from <a href="index.php?title=<?php echo htmlspecialchars(urlencode($redirectFrom)); ?>&noredirect"><?php echo htmlspecialchars($redirectFrom); ?></a></small>
        <script>
        const actualPageName = <?php echo json_encode($originalPageName); ?>;
        const url = new URL(location.href);
        url.searchParams.set('title', actualPageName);
        window.history.replaceState({}, document.title, url);</script>
        </div><?php
        }
        ?>
        <div id="page-content"><?php echo $output; ?></div>
        <footer><?php require 'footer.html'; ?></footer>
    </body>
</html>