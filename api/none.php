<?php 
function api() {
    set_response('Nothing in, nothing out.');
    add_error('wrongdata', 'Specify an action parameter of something other than none to get actual results.', true);
}