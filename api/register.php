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
    if (!isset($args['username'], $args['password'])) {
        return add_error("wronginput", "username or password missing", true);
    }
    require_once __DIR__ . "/../accounts.php";
    if (createAccount($args['username'], $args['password'])) {
        return set_response("Account created");
    }
    return add_error("sorry", "Username taken.");
}