<?php
$title = 'Logging out...';
if (!isset($_POST['logout'])) {
    ?>
    <p>To prevent malicious logout attacks, please press the button below to log yourself 
        out. </p>
    <form action="index.php?title=Special:logout" method="post">
        <input name="logout" value="Log out" type="submit" />
    </form>
    <?php
    return;
}
session_destroy();
$title = 'Logged out';
?>
<p>You have been logged out. You may press the BACK button to continue browsing, but please 
    be aware that due to caching, some pages may still display that you are logged in. You
    can clear your cache to resolve this issue.</p>