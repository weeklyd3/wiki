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
 
$title = 'Patrol recent changes';
?>
<p>You can review recent edits here.</p>
<noscript id="no-js">
<?php 
require_once __DIR__ . "/getrecentedits.php";
?>
</noscript>
<div id="yes-js" hidden="hidden">
    <button onclick="refreshChanges()" id="refresh">Refresh list</button>
    <div id="recentChangesArea">Loading...</div>
</div>
<script>
    document.getElementById('no-js').hidden = 'hidden';
    document.getElementById('yes-js').hidden = '';
    function refreshChanges() {
        document.getElementById('recentChangesArea').style.opacity = '0.5';
        document.querySelector('#refresh').disabled = 'disabled';
        fetch('index.php?title=Special:getrecentedits&raw=true')
        .then(function(response) {
            return response.text();
        })
        .then(function(text) {
            document.getElementById('recentChangesArea').style.opacity = '1';
            document.querySelector('#refresh').disabled = '';
            document.getElementById('recentChangesArea').innerHTML = text;
        });
    }
    refreshChanges();
</script>