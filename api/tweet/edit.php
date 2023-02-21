<?php session_start();
require "../../db.php";

$email = $_SESSION['email'];

if(!isset($_POST['tweet_id'])) {
    header("HTTP/1.1 422 Unprocessable Entity");
    echo json_encode(['status' => 422, 'message' => "Tweet not determined."]);
    return;
}

if(!isset($_POST['tweet']) && !isset($_FILES['image'])) {
    header("HTTP/1.1 422 Unprocessable Entity");
    echo json_encode(['status' => 422, 'message' => "Empty tweet."]);
    return;
}

$tweet = $_POST['tweet'];
$tweet_id = $_POST['tweet_id'];

$get_tweet = mysqli_query($database_connection, "SELECT * FROM tweets WHERE id = '$tweet_id'");

if(mysqli_num_rows($get_tweet) < 1) {
    header("HTTP/1.1 404 Not Found");
    echo json_encode(['status' => 404, 'message' => "Tweet not found."]);
    return;
}

$tweet_result = mysqli_fetch_assoc($get_tweet);

$get_user_id = mysqli_query($database_connection, "SELECT id FROM users WHERE email = '$email'");
$user_id = mysqli_fetch_assoc($get_user_id)['id'];

if($tweet_result['user_id'] != $user_id) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['status' => 403, 'message' => 'You are not the owner of this tweet']);
    return;
}

/**
 * Mengatur ulang tag
 */
mysqli_query($database_connection, "DELETE FROM tag_tweet WHERE tweet_id = '$tweet_id'");
$tags = [];
$tweet_split = explode("#", $tweet);

if(count($tweet_split) > 1) {
    foreach($tweet_split as $key=>$value) {
        if($key == 0) {
            continue;
        }

        $tags[] = trim($value);
    }
}

if(!empty($_FILES['image'])) {
    if(!empty($tweet_result['media'])) {
        unlink(__DIR__."/../../attachments/".$tweet_result['media']);
    }
    
    $file_name = explode(".", $_FILES['image']['name']);
    $file_extension = end($file_name);

    $new_file_name = time() . "." . $file_extension;

    move_uploaded_file($_FILES['image']['tmp_name'], __DIR__."/../../attachments/".$new_file_name);

    $insert_query = mysqli_query($database_connection, "UPDATE tweets SET tweet = '$tweet', media = '$new_file_name', updated_at = now() WHERE id = '$tweet_id'");
} else {
    $insert_query = mysqli_query($database_connection, "UPDATE tweets SET tweet = '$tweet', updated_at = now() WHERE id = '$tweet_id'");
}

/**
 * Tambahkan tag jika ada.
 */
$new_tweet_query = mysqli_query($database_connection, "SELECT * FROM tweets WHERE user_id = $user_id AND tweet = '$tweet' ORDER BY id DESC LIMIT 1");
$new_tweet_result = mysqli_fetch_assoc($new_tweet_query);

$tweet_id = $new_tweet_result['id'];

foreach($tags as $tag) {
    if(empty(find_tag_id($tag))) {
        create_tag($tag);
    } 

    $tag_id = find_tag_id($tag);
    
    insert_new_tweet($tag_id, $tweet_id);
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
 * Function untuk membuat row baru di table `tag_tweet`
 */
function insert_new_tweet($tag_id, $tweet_id) {
    global $database_connection;

    mysqli_query($database_connection, "INSERT INTO tag_tweet(tag_id, tweet_id) VALUES('$tag_id', '$tweet_id')");
    return;
};

header("HTTP/1.1 200 OK");
echo json_encode(['status' => 200, 'message' => 'Tweet updated.']);