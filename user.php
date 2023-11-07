<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: X-Requested-with");
header("content-Type: application/json");

require_once("db.php");
$user = new Database();

$api = $_SERVER["REQUEST_METHOD"];

// - - - Functions for GET, PUT, and DELETE
function extractAscArr ($user){
    // Store in form of Associative Array ($post_input)
    parse_str(file_get_contents('php://input'), $data);

    // Transform to associative array
    $postData = array();
    foreach ($data as $key => $value) {
        $postData[$key] = $value;
        $postData[$key] = $user->test_input($postData[$key]);
    }
    return $postData;
}

function extractID ($user, $postData) {    
    // Transform all ID into associative array. in case contains multiple ID (UID, JID, CID, LID)
    $idArray = array();
    foreach (array_keys($postData) as $key){
        if (substr($key, -2) == "ID"){
            $idArray[$key] = $postData[$key];
            array_shift($postData);
        }
    }
    return $idArray;
}
// - - - Functions for GEt, PUT, and DELETE

if ($api == "GET") {

    $postData = extractAscArr($user);
    $tableName = $postData['tableName'];
    array_shift($postData);
    $idArray = extractID($user, $postData);

    $data = $user->fetch($tableName, $idArray);

    echo json_encode($data);
}

if ($api == "POST") {
    // Login
    if (isset($_POST["login"])) {
        $username = $_POST["username"];
        $password = $_POST["password"];
        if ($user->login($username, $password)){
            echo $user->message("Login succesfully", false);
        } else {
            echo $user->message("Failed to login. Please check your Username or Password", true);
        }
    } else { 
        // POST new row into table
        $postData = array();
        foreach ($_POST as $key => $value) {
            $postData[$key] = $value;
            $postData[$key] = $user->test_input($postData[$key]);
        }

        $tableName = $postData['tableName'];
        array_shift($postData);

        // encode password
        if ($tableName == "User") {
            $postData["password"] = password_hash($postData["password"], PASSWORD_DEFAULT);
        }

        if ($user->insert($tableName, $postData)) {
            echo $user->message("added new row of {$tableName} successfully", false);
        } else {
            echo $user->message("Failed to add a new row to {$tableName}", true);
        }
    }
}

if ($api == "PUT") {

    $postData = extractAscArr($user);
    $tableName = $postData['tableName'];
    array_shift($postData);
    $idArray = extractID($user, $postData);

    if ($idArray != null) {
        if ($user->update($tableName, $idArray, $postData)) {
            echo $user->message("{$tableName} with specific id is updated succesfully", false);
        } else {
            echo $user->message("Failed to update a {$tableName} with specific id", true);
        }
    } else {
        echo $user->message("{$tableName}'s id not found", true);
    }
}
if ($api == "DELETE") {

    $postData = extractAscArr($user);
    $tableName = $postData['tableName'];
    $idArray = extractID($user, $postData);

    if ($idArray != null) {
        if ($user->delete($tableName, $idArray)) {
            echo $user->message("{$tableName} table with specific id is deleted succesfully", false);
        } else {
            echo $user->message("Failed to delete row in {$tableName}", true);
        }
    } else {
        echo $user->message("{$tableName} or id not found", true);
    }
}
