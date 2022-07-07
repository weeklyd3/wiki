<?php 
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