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
 
function api(string $action, array $arguments) {
    if (!isset($arguments['title']) && !isset($arguments['titles'])) {
        return add_error("wrongdata", "No title specified, use the title URL parameter next time", true);
    }
    if (isset($arguments['title'], $arguments['titles'])) {
        add_warning("wrongdata", "'title' and 'titles' are both specified. 'titles' will be overriden.");
    }
    if (isset($arguments['titles'])) {
        if (json_decode($arguments['titles']) === null) {
            add_warning("wrongdata", "'titles' is not valid JSON.");
            if (!isset($arguments['title'])) return;
        } else {
            if (!is_array(json_decode($arguments['titles']))) {
                add_warning("wrongdata", "'titles' is not a JSON array.");
                if (!isset($arguments['title'])) return;
            } else {
                if (!count(json_decode($arguments['titles']))) {
                    add_warning('wrongdata', "Zero titles passed in titles parameter.");
                }
            }
        }
    }
    require_once __DIR__ . "/../markdown/parsedown/parsedown.php";
    $Parsedown = new Parsedown;
    $titles = array();
    if (isset($arguments['title'])) $titles = array($arguments['title']);
    else $titles = json_decode($arguments['titles']);
    $o = new stdClass;
    $pageInfo = json_decode(file_get_contents(__DIR__ . "/../pages/page2ID.json"));
    foreach ($titles as $title) {
        $res = new stdClass;
        $res->title = $title;
        $res->exists = isset($pageInfo->$title);
        if (!$res->exists) {
            $o->$title = $res;
            continue;
        }
        $res->pageID = $pageInfo->$title;
        $pageid = $res->pageID;
        $res->markdown = file_get_contents(__DIR__ . "/../pages/data/$pageid/page.md");
        $res->revisions = json_decode(file_get_contents(__DIR__ . "/../pages/data/$pageid/revisions.json"));
        $res->html = $Parsedown->text($res->markdown);
        $o->$title = $res;
    }
    set_response($o);
}
