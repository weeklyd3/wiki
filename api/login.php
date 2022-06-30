<?php 
function api(string $action, array $args) {
    if (!isset($args['username'], $args['password'])) return add_error("wronginput", "Username or password missing", true);
    require_once __DIR__ . "/../accounts.php";
    switch (login($args['username'], $args['password'])) {
        case 0:
            return set_response("Logged in successfully");
        case 1:
            return add_error("authfailure", "Username incorrect");
        case 2:
            return add_error("authfailure", "Password incorrect");
    }
}