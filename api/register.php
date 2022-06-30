<?php 
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