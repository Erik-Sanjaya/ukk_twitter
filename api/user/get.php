<?php session_start();
require "../../db.php";

$user_id = $_GET['id'];
$select_user = mysqli_query($database_connection, "SELECT id, username, bio, profile_picture FROM users WHERE id = '$user_id'");
$result_user = mysqli_fetch_assoc($select_user);

$result = [];
$result['profile'] = $result_user;

if(empty($result_user)) {
    header("HTTP/1.1 404 Not Found");
    echo json_encode(['status' => 404, 'message' => 'User not found.']);
    return;
}

$select_tweets = mysqli_query($database_connection, "SELECT * FROM tweets WHERE user_id = '$user_id' ORDER BY id DESC");

while($row = mysqli_fetch_assoc($select_tweets)) {
    $result['tweets'][] = $row;
}

header("HTTP/1.1 200 OK");
echo json_encode($result);