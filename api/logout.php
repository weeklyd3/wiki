<?php 
function api() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return add_error("wronginput", "Only POST requests are allowed. Got " . $_SERVER['REQUEST_METHOD'] . ".");
    unset($_SESSION);
    session_destroy();
    set_response("Logged out.");
}