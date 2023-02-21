<?php session_start();
require "../../db.php";

if(empty($_POST['username']) && empty($_POST['bio']) && empty($_FILES['image'])) {
    header("HTTP/1.1 422 Unprocessable Entity");
    echo json_encode(['status' => 422, 'message' => "Empty edit."]);
    return;
}


$email = $_SESSION['email'];
$select_user = mysqli_query($database_connection, "SELECT * FROM users WHERE email = '$email'");
$result_user = mysqli_fetch_assoc($select_user);
$user_id = $result_user['id'];

if(!empty($_POST['username'])) {
    $username = $_POST['username'];
    mysqli_query($database_connection, "UPDATE users SET username = '$username' WHERE id = '$user_id'");
}

if(!empty($_POST['bio'])) {
    $bio = $_POST['bio'];
    mysqli_query($database_connection, "UPDATE users SET bio = '$bio' WHERE id = '$user_id'");
}

if(!empty($_FILES['image'])) {
    if(!empty($result_user['profile_picture'])) {
        unlink(__DIR__."/../../profile_pictures/".$result_user['profile_picture']);
    }

    $file_name = explode(".", $_FILES['image']['name']);
    $file_extension = end($file_name);

    $new_file_name = time() . "." . $file_extension;

    move_uploaded_file($_FILES['image']['tmp_name'], __DIR__."/../../profile_pictures/".$new_file_name);

    mysqli_query($database_connection, "UPDATE users SET profile_picture = '$new_file_name' WHERE id = '$user_id'");
}

header("HTTP/1.1 200 OK");
echo json_encode(['status' => 200, 'message' => 'Profile updated.']);