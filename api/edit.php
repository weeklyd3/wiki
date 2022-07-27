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
 
function api(string $action, array $args) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return add_error("wrongmethod", "Only POST requests are allowed");
    }
    if (!isset($_GET['title'])) {
        return add_error('wronginput', "No title specified.");
    }
    require_once __DIR__ . "/../pages.php";
    if (!(canEditPage($_GET['title'])[0])) {
        $can = canEditPage($_GET['title']);
        return add_error("accessdenied", "The page is protected or you are not logged in. Error code: {$can[1]}");
    }
    modifyPage($_GET['title'], $_POST['contents'] ?? page_get_contents($_GET['title']), $_GET['summary'] ?? '');
    return set_response("Page edited.");
}