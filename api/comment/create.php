<?php session_start();
require "../../db.php";

$email = $_SESSION['email'];

/**
 * Cek apakah `$_POST` memiliki data yang dibutuhkan
 */
if(!isset($_POST['tweet_id'])) {
    header("HTTP/1.1 422 Unprocessable Entity");
    echo json_encode(['status' => 422, 'message' => "Tweet not determined."]);
    return;
}

if(!isset($_POST['comment']) && !isset($_FILES['image'])) {
    header("HTTP/1.1 422 Unprocessable Entity");
    echo json_encode(['status' => 422, 'message' => "Empty comment."]);
    return;
}


$comment = $_POST['comment'];
$tweet_id = $_POST['tweet_id'];

/**
 * Cek apakah tweet ada di dalam table `tweets`
 */
$get_tweet = mysqli_query($database_connection, "SELECT * FROM tweets WHERE id = '$tweet_id'");
if(mysqli_num_rows($get_tweet) < 1) {
    header("HTTP/1.1 404 Not Found");
    echo json_encode(['status' => 404, 'message' => "Tweet not found."]);
    return;
}


$tweet_id = mysqli_fetch_assoc($get_tweet)['id'];
$get_user_id = mysqli_query($database_connection, "SELECT id FROM users WHERE email = '$email'");
$user_id = mysqli_fetch_assoc($get_user_id)['id'];

/**
 * Membelah comment menjadi bagian antara text dan tags
 */
$tags = [];
$comment_split = explode("#", $comment);

if(count($comment_split) > 1) {
    foreach($comment_split as $key=>$value) {
        if($key == 0) {
            continue;
        }

        $tags[] = trim($value);
    }
}

/**
 * Image upload handling
 */
if(!empty($_FILES['image'])) {
    $file_name = explode(".", $_FILES['image']['name']);
    $file_extension = end($file_name);

    $new_file_name = time() . "." . $file_extension;

    move_uploaded_file($_FILES['image']['tmp_name'], __DIR__."/../../attachments/".$new_file_name);

    $insert_query = mysqli_query($database_connection, "INSERT INTO comments(comment, media, tweet_id, user_id, created_at, updated_at) VALUES('$comment', '$new_file_name', $tweet_id, $user_id,  now(), now())");
} else {
    $insert_query = mysqli_query($database_connection, "INSERT INTO comments(comment, tweet_id, user_id, created_at, updated_at) VALUES('$comment', $tweet_id, $user_id, now(), now())");
}

/**
 * Tambahkan tag jika ada.
 */
$new_comment_query = mysqli_query($database_connection, "SELECT * FROM comments WHERE user_id = $user_id AND comment = '$comment' AND tweet_id = '$tweet_id' ORDER BY id DESC LIMIT 1");
$new_comment_result = mysqli_fetch_assoc($new_comment_query);

$comment_id = $new_comment_result['id'];

foreach($tags as $tag) {
    if(empty(find_tag_id($tag))) {
        create_tag($tag);
    } 

    $tag_id = find_tag_id($tag);
    insert_new_comment($tag_id, $comment_id);
}


/**
 * Function untuk mengecek jika tag sudah tersimpan di table `tags`
 */
function find_tag_id($tag) {
    global $database_connection;

    $select_tag = mysqli_query($database_connection, "SELECT * FROM tags WHERE name = '$tag'");
    if(mysqli_num_rows($select_tag) > 0) {
        return mysqli_fetch_assoc($select_tag)['id'];
    } else {
        return null;
    }
}

/**
 * Function untuk membuat tag baru di dalam table `tags`
 */
function create_tag($tag) {
    global $database_connection;

    mysqli_query($database_connection, "INSERT INTO tags(name, created_at, updated_at) VALUES('$tag', now(), now())");
    return;
}

/**
 * Function untuk membuat row baru di table `tag_comment`
 */
function insert_new_comment($tag_id, $comment_id) {
    global $database_connection;

    mysqli_query($database_connection, "INSERT INTO tag_comment(tag_id, comment_id) VALUES('$tag_id', '$comment_id')");
    return;
};

header("HTTP/1.1 201 Created");
echo json_encode(['status' => 201, 'message' => 'Comment created.']);