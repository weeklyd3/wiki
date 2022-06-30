<?php
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    die('<h1>STALP HECKING</h1>');
}
function userinfo(int $id): ?object {
    if (!is_dir(__DIR__ . "/users/data/$id")) return null;
    return json_decode(file_get_contents(__DIR__ . "/users/data/$id/user.json"));
}
/* 
    Return codes:
    true:  success
    false: faliure - username taken
*/
require_once __DIR__ . "/log/log.php";
function createAccount(string $username, string $password, array $groups = array()): bool {
    $currentUID = json_decode(file_get_contents(__DIR__ . '/users/currentID.json'));
    fwrite(fopen(__DIR__ . '/users/currentID.json', 'w+'), $currentUID + 1);
    $name2id = json_decode(file_get_contents(__DIR__ . '/users/name2ID.json'));
    if (isset($name2id->$username)) return false;
    $name2id->$username = $currentUID;
    fwrite(fopen(__DIR__ . '/users/name2ID.json', 'w+'), json_encode($name2id));
    mkdir(__DIR__ . "/users/data/$currentUID", 0777, true);
    $user = new stdClass;
    $user->creationDate = time();
    $user->username = $username;
    $user->password = password_hash($password, PASSWORD_DEFAULT);
    foreach ($groups as $group) {
        $group2id = json_decode(file_get_contents(__DIR__ . "/users/group2ID.json"));
        new logEntry($currentUID, $currentUID, null, "userGroupChange", "+$group", "Automatically added while creating account");
        if (isset($group2id->$group)) {
            $id = $group2id->$group;
            $groupInfo = json_decode(file_get_contents(__DIR__ . "/users/groups/$id/info.json"));
            array_push($groupInfo->members, $currentUID);
            fwrite(fopen(__DIR__ . "/users/groups/$id/info.json", "w+"), json_encode($groupInfo));
            continue;
        }
        new logEntry($currentUID, $currentUID, null, "userGroupCreated", "Name: $group", "Group created while creating account");
        $currentGroupID = json_decode(file_get_contents(__DIR__ . "/users/currentGroupID.json"));
        $group2id->$group = $currentGroupID;
        fwrite(fopen(__DIR__ . "/users/group2ID.json", "w+"), json_encode($group2id));
        fwrite(fopen(__DIR__ . "/users/currentGroupID.json", "w+"), $currentGroupID + 1);
        mkdir(__DIR__ . "/users/groups/$currentGroupID", 0777, true);
        $groupi = new stdClass;
        $groupi->name = $group;
        $groupi->created = time();
        $groupi->members = array($currentUID);
        fwrite(fopen(__DIR__ . "/users/groups/$currentGroupID/info.json", "w+"), json_encode($groupi));
    }
    new logEntry($currentUID, null, null, "userCreated", "User $username was created", "Created using the createAccount PHP function");
    $group2id = json_decode(file_get_contents(__DIR__ . "/users/group2ID.json"));
    $groupIDarray = array();
    foreach ($groups as $group) array_push($groupIDarray, $group2id->$group);
    $user->groups = $groupIDarray;
    fwrite(fopen(__DIR__ . "/users/data/$currentUID/user.json", "w+"), json_encode($user));
    return true;
}
/* 
    Return values:
    0 - success
    1 - bad username
    2 - bad password
*/
function login(string $username, string $password): int {
    $user2id = json_decode(file_get_contents(__DIR__ . '/users/name2ID.json'));
    if (!isset($user2id->$username)) return 1;
    $userid = $user2id->$username;
    $passwordJSON = json_decode(file_get_contents(__DIR__ . "/users/data/$userid/user.json"));
    $hash = $passwordJSON->password;
    if (password_verify($password, $hash)) {
        $_SESSION['username'] = $username;
        $_SESSION['userid'] = $userid;
        return 0;
    }
    return 2;
}
function userlink(string $username): string {
    return '<a href="index.php?title=User:' . htmlspecialchars(urlencode($username)) . '">' . htmlspecialchars($username) . '</a> (<a href="index.php?title=User+talk:' . htmlspecialchars(urlencode($username)) . '">leave a message</a>)';
}
function getUserGroups(?int $user, bool $plain = false): ?array {
    if (!isset($user)) return array();
    if (!userinfo($user)) return null;

    $groups = userinfo($user)->groups;
    $gr = array();
    foreach ($groups as $group) {
        $info = json_decode(file_get_contents(__DIR__ . "/users/groups/$group/info.json"));
        if ($plain) {
            array_push($gr, $info->name);
            continue;
        }
        array_push($gr, $info);
    }
    return $gr;
}