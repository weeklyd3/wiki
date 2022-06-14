<?php
if (!defined('installed')) return;
require_once 'settings.php';
define('pagename', $originalPageName);
?>
<header id="header">
    <a href="index.php" id="back2home"><?php echo htmlspecialchars($sitename); ?></a>
    <div class="floatright">
        <?php 
        if (isset($_SESSION['username'])) {
            echo userlink($_SESSION['username']);
            $currentUserID = $_SESSION['userid'];
            if (file_exists(__DIR__ . "/users/data/$currentUserID/newMessages.json")) {
                ?><a class="newmessages" href="index.php?title=User+talk:<?php echo htmlspecialchars(urlencode($_SESSION['username'])); ?>">You have new messages</a><?php
            }
            ?> (<a href="index.php?title=Special:logout">log out</a>)<?php
        } else {
            ?> not logged in (<a href="index.php?title=Special:login">log in</a>) (<a href="index.php?title=Special:signup">create account</a>)<?php
        }
        ?>
    </div>
    <div class="clear"></div>
    <div id="thispage">
        <form action="index.php" class="inline">
            <input type="hidden" name="title" value="<?php echo htmlspecialchars(pagename); ?>" />
            <input type="submit" value="View" />
        </form>
        <form action="index.php" class="inline">
            <input type="hidden" name="title" value="<?php echo htmlspecialchars(pagename); ?>" />
            <input type="hidden" name="action" value="edit" />
            <input type="submit" value="Edit" title="<?php if (substr(pagename, 0, 8) === "Special:") { ?>You cannot edit special pages." disabled="disabled" <?php } else { ?>Edit this page" <?php } ?>/>
        </form>
        <form action="index.php" class="inline">
            <input type="hidden" name="title" value="<?php echo htmlspecialchars(pagename); ?>" />
            <input type="hidden" name="action" value="history" />
            <input type="submit" value="Revisions" title="<?php if (substr(pagename, 0, 8) === "Special:") { ?>No history is available for special pages." disabled="disabled" <?php } else { ?>View past revisions of this page" <?php } ?>/>
        </form>
        <div class="floatright">
            <form action="index.php" method="GET">
                <input type="hidden" name="title" value="Special:search" />
                <label><span class="hidden2eyes">Search this wiki: </span><input type="search" name="query" placeholder="Search in <?php echo htmlspecialchars($sitename); ?>" /></label>
                <input type="submit" value="Search" />
            </form>
        </div>
    </div>
    <div><a href="index.php?title=Special:recentedits">Recent activity</a> - <a href="index.php?title=Special:upload">Upload file</a></div>
</header>