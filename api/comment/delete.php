<?php session_start();
require "../../db.php";

$email = $_SESSION['email'];
$select_user = mysqli_query($database_connection, "SELECT * FROM users WHERE email = '$email'");
$user_id = mysqli_fetch_assoc($select_user)['id'];

$comment_id = $_GET['id'];
$select_comment = mysqli_query($database_connection, "SELECT * FROM comments WHERE id = '$comment_id'");
$result_comment = mysqli_fetch_assoc($select_comment);

/**
 * Cek apakah user adalah pemilik comment.
 */
if($result_comment['user_id'] != $user_id) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['status' => 403, 'message' => 'You are not the owner of this comment.']);
    return;
}

/**
 * Cek apakah comment memiliki media.
 */
if(!empty($result_comment['media'])) {
    unlink(__DIR__."/../../attachments/".$result_comment['media']);
}

mysqli_query($database_connection, "DELETE FROM tag_comment WHERE comment_id = '$comment_id'");
$delete_comment = mysqli_query($database_connection, "DELETE FROM comments WHERE id = '$comment_id'");

header("HTTP/1.1 200 OK");
echo json_encode(['status' => 200, 'message' => 'Tweet deleted.']);