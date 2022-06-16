<?php
$title = 'Create account';
if (isset($_POST['signup'])) {
    if ($_POST['password'] === $_POST['password2']) {
        if (createAccount($_POST['username'], $_POST['password'], array($newUserGroup))) {
            $title = 'Welcome!';
            login($_POST['username'], $_POST['password']);
            ?><p>You have created an account successfully and logged in to it. Please hit the BACK button and start browsing the wiki!</p><?php
        } else {
            ?><div class="error">The username you want is taken, please try another one!</div><?php
        }
    } else {
        ?><div class="error">The two passwords you entered don't match, please try that again!</div><?php
    }
}
?>
<p>Note: You DO NOT need an account to read pages on this wiki. You only need an account to 
   edit and create pages on this wiki.</p>
<form action="index.php?title=Special:signup" method="post">
   <label>Enter your username:<div></div>
<input name="username" required="required" />
</label>
<div></div>
<label>
   Enter your password:<div></div>
   <input name="password" type="password" required="required" />
</label>
<div></div>
<label>
   Enter your password (again):<div></div>
   <input name="password2" type="password" required="required" />
</label>
<div></div>
<input type="submit" name="signup" value="Create my account" />
</form>