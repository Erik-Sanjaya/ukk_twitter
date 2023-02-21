<?php session_start();
require "../../db.php";

// SELECT `users`
$email = $_SESSION['email'];
$select_user = mysqli_query($database_connection, "SELECT * FROM users WHERE email = '$email'");
$user_id = mysqli_fetch_assoc($select_user)['id'];

// Konfirmasi ada `tweet`nya
$tweet_id = $_GET['id'];
$select_tweet = mysqli_query($database_connection, "SELECT * FROM tweets WHERE id = '$tweet_id'");
$result_tweet = mysqli_fetch_assoc($select_tweet);

if($result_tweet['user_id'] != $user_id) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['status' => 403, 'message' => 'You are not the owner of this tweet']);
    return;
}

// Cek apakah ada media
if(!empty($result_tweet['media'])) {
    unlink(__DIR__."/../../attachments/".$result_tweet['media']);
}
$delete_tweet = mysqli_query($database_connection, "DELETE FROM tweets WHERE id = '$tweet_id'");

// Delete comments
$select_comments = mysqli_query($database_connection, "SELECT * FROM comments WHERE tweet_id = '$tweet_id'");

while($row = mysqli_fetch_assoc($select_comments)) {
    $id = $row['id'];
    if(!empty($row['media'])) {
        unlink(__DIR__."/../../attachments/".$row['media']);
    }

    mysqli_query($database_connection, "DELETE FROM tag_comment WHERE comment_id = '$id'");
    mysqli_query($database_connection, "DELETE FROM comments WHERE id = '$id'");
}

// Delete tags
mysqli_query($database_connection, "DELETE from tag_tweet WHERE tweet_id = $tweet_id");

header("HTTP/1.1 200 OK");
echo json_encode(['status' => 200, 'message' => 'Tweet deleted.']);
