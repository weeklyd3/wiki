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
function createAccount(string $username, string $password, array $groups = array()): bool {
    $currentUID = json_decode(file_get_contents('users/currentID.json'));
    fwrite(fopen('users/currentID.json', 'w+'), $currentUID + 1);
    $name2id = json_decode(file_get_contents('users/name2ID.json'));
    if (isset($name2id->$username)) return false;
    $name2id->$username = $currentUID;
    fwrite(fopen('users/name2ID.json', 'w+'), json_encode($name2id));
    mkdir(__DIR__ . "/users/data/$currentUID", 0777, true);
    $user = new stdClass;
    $user->creationDate = time();
    $user->username = $username;
    $user->password = password_hash($password, PASSWORD_DEFAULT);
    foreach ($groups as $group) {
        $group2id = json_decode(file_get_contents("users/group2ID.json"));
        if (isset($group2id->$group)) {
            $id = $group2id->$group;
            $groupInfo = json_decode(file_get_contents("users/groups/$id/info.json"));
            array_push($groupInfo->members, $currentUID);
            fwrite(fopen("users/groups/$id/info.json", "w+"), json_encode($groupInfo));
            continue;
        }
        $currentGroupID = json_decode(file_get_contents("users/currentGroupID.json"));
        $group2id->$group = $currentGroupID;
        fwrite(fopen("users/group2ID.json", "w+"), json_encode($group2id));
        fwrite(fopen("users/currentGroupID.json", "w+"), $currentGroupID + 1);
        mkdir(__DIR__ . "/users/groups/$currentGroupID", 0777, true);
        $groupi = new stdClass;
        $groupi->name = $group;
        $groupi->created = time();
        $groupi->members = array($currentUID);
        fwrite(fopen("users/groups/$currentGroupID/info.json", "w+"), json_encode($groupi));
    }
    $group2id = json_decode(file_get_contents("users/group2ID.json"));
    $groupIDarray = array();
    foreach ($groups as $group) array_push($groupIDarray, $group2id->$group);
    $user->groups = $groupIDarray;
    fwrite(fopen("users/data/$currentUID/user.json", "w+"), json_encode($user));
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
    return 1;
}
function userlink(string $username): string {
    return '<a href="index.php?title=User:' . htmlspecialchars(urlencode($username)) . '">' . htmlspecialchars($username) . '</a> (<a href="index.php?title=User+talk:' . htmlspecialchars(urlencode($username)) . '">leave a message</a>)';
}