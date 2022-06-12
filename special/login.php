<?php
$title = 'Log in';
if (isset($_POST['login'])) {
    switch (login($_POST['username'], $_POST['password'])) {
        case 0:
            ?><p>You have been logged in. Please press the BACK button and start 
                browsing the wiki!
            </p><?php
            return;
            break;
        case 1:
            ?><div class="error">That username doesn't look correct. Usernames are case-sensitive!</div><?php
            break;
        case 2:
            ?><div class="error">Your password is incorrect.</div><?php
            break;
    }
}
?>
<p>Enter your username and password below to log in.</p>
<form action="index.php?title=Special:login" method="post">
    <label>Enter your username:<div></div>
<input name="username" />
</label>
<div></div>
<label>Enter your password:<div></div>
<input type="password" name="password" />
</label>
<div></div>
<input type="submit" value="Log in!" name="login" />
</form>
<p>If you do not have an account, you may <a href="index.php?title=Special:signup">create 
    an account.</a></p>