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
?>
You entered an invalid special page name. A list of all valid 
special pages is below:
<ul>
<?php 
$title = 'Bad special page';
global $specialPages;
foreach ($specialPages as $page => $filename) {
    if ($page === '.' || $page === '..') continue;
    ?><li><a href="index.php?title=Special:<?php echo htmlspecialchars(urlencode($page)); ?>">Special:<?php echo htmlspecialchars($page); ?></a></li><?php
}
?></ul>