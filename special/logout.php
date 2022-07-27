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