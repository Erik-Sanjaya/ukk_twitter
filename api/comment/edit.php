<?php session_start();
require "../../db.php";

$email = $_SESSION['email'];

/**
 * Cek apakah `$_POST` memiliki data yang dibutuhkan
 */
if(!isset($_POST['comment_id'])) {
    header("HTTP/1.1 422 Unprocessable Entity");
    echo json_encode(['status' => 422, 'message' => "Comment not determined."]);
    return;
}

if(!isset($_POST['comment']) && !isset($_FILES['image'])) {
    header("HTTP/1.1 422 Unprocessable Entity");
    echo json_encode(['status' => 422, 'message' => "Empty comment."]);
    return;
}

$comment = $_POST['comment'];
$comment_id = $_POST['comment_id'];

$get_comment = mysqli_query($database_connection, "SELECT * FROM comments WHERE id = '$comment_id'");

if(mysqli_num_rows($get_comment) < 1) {
    header("HTTP/1.1 404 Not Found");
    echo json_encode(['status' => 404, 'message' => "Comment not found."]);
    return;
}

$comment_result = mysqli_fetch_assoc($get_comment);

$get_user_id = mysqli_query($database_connection, "SELECT id FROM users WHERE email = '$email'");
$user_id = mysqli_fetch_assoc($get_user_id)['id'];

if($comment_result['user_id'] != $user_id) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['status' => 403, 'message' => 'You are not the owner of this comment']);
    return;
}



/**
 * Mengatur ulang tag dalam comment
 */
mysqli_query($database_connection, "DELETE FROM tag_comment WHERE comment_id = '$comment_id'");
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

if(!empty($_FILES['image'])) {
    if(!empty($comment_result['media'])) {
        unlink(__DIR__."/../../attachments/".$comment_result['media']);
    }

    $file_name = explode(".", $_FILES['image']['name']);
    $file_extension = end($file_name);

    $new_file_name = time() . "." . $file_extension;

    move_uploaded_file($_FILES['image']['tmp_name'], __DIR__."/../../attachments/".$new_file_name);

    $insert_query = mysqli_query($database_connection, "UPDATE comments SET comment = '$comment', media = '$new_file_name', updated_at = now() WHERE id = '$comment_id'");
} else {
    $insert_query = mysqli_query($database_connection, "UPDATE comments SET comment = '$comment', updated_at = now() WHERE id = '$comment_id'");
}

/**
 * Tambahkan tag jika ada.
 */
$new_comment_query = mysqli_query($database_connection, "SELECT * FROM comments WHERE user_id = $user_id AND comment = '$comment' ORDER BY id DESC LIMIT 1");
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

header("HTTP/1.1 200 OK");
echo json_encode(['status' => 200, 'message' => 'Comment updated.']);