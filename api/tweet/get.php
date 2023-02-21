<?php session_start();
require "../../db.php";

$select_all_tweets = mysqli_query($database_connection, "SELECT * FROM tweets ORDER BY id DESC");

$result = [];

while($row = mysqli_fetch_assoc($select_all_tweets)) {
    $comments = get_all_comments_of_tweet($row['id']);
    $user = get_username_and_profile($row['user_id']);

    $row['comments'] = $comments;
    $row['user'] = $user;
    $result[] = $row;
    
}



function get_all_comments_of_tweet($tweet_id) {
    global $database_connection;

    $result = [];

    $select_all_comments = mysqli_query($database_connection, "SELECT * FROM comments WHERE tweet_id = '$tweet_id'");
    while($row = mysqli_fetch_assoc($select_all_comments)) {
        $user = get_username_and_profile($row['user_id']);
        $row['user'] = $user;

        $result[] = $row;
    }

    return $result;
}

function get_username_and_profile($user_id) {
    global $database_connection;

    $select_user = mysqli_query($database_connection, "SELECT username, profile_picture FROM users WHERE id = '$user_id'");
    $result_user = mysqli_fetch_assoc($select_user);

    return $result_user;
}

echo json_encode($result);