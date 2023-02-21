<?php session_start();

require "../../db.php";
$body = json_decode(file_get_contents("php://input"));

if(empty($body->username) || empty($body->email) || empty($body->password) || empty($body->confirm_password)) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['status' => 400, 'message' => 'Missing field.']);
    return;
}

if($body->confirm_password != $body->password) {
    header("HTTP/1.1 406 Not Acceptable");
    echo json_encode(['status' => 406, 'message' => 'Password and confirmation password conflict.']);
    return;
}

$username = $body->username;
$email = $body->email;
$password = $body->password;

$query_user = mysqli_query($database_connection, "SELECT * FROM users WHERE email = '$email' LIMIT 1");
$result_user = mysqli_fetch_assoc($query_user);

if(isset($result_user)) {
    header("HTTP/1.1 409 Conflict");
    echo json_encode(['status' => 409, 'message' => 'This email has been registered.']);
    return;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$insert_user = mysqli_query($database_connection, "INSERT INTO users(username, email, password) VALUES('$username', '$email', '$password_hash')");

$_SESSION['email'] = $email;

$query_user_id = mysqli_query($database_connection, "SELECT id FROM users WHERE email = '$email' LIMIT 1");
$result_user_id = mysqli_fetch_assoc($query_user_id);

header("HTTP/1.1 201 Created");
echo json_encode(['status' => 201, 'message' => 'Account created.', 'user_id' => $result_user_id['id']]);
return;